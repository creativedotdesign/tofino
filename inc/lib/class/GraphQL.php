<?php

/**
 * GraphQL
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\GraphQL;

class GraphQL
{
  public function __construct()
  {
    // Check plugin is active
    if (!class_exists('WPGraphQL')) {
      return;
    }

    add_filter('graphql_register_types', [$this, 'register']);
    add_filter('graphql_PostObjectsConnectionOrderbyEnum_values', [$this, 'add_offset_ordering']);
    add_filter('graphql_input_fields', [$this, 'add_offset_pagination'], 10, 2);
    add_filter('graphql_post_object_connection_query_args', [$this, 'add_query_args'], 10, 5);
    add_filter('graphql_post_object_connection_query_args', [$this, 'filter_post_by_term_ids'], 10, 5);
    add_filter('graphql_connection_page_info', [$this, 'update_page_info'], 10, 2); // Hook for page info
  }

  public function register()
  {
    register_graphql_object_type('OffsetPagination', [
      'fields' => [
        'total' => [
          'type' => 'Int',
          'description' => 'Total number of items'
        ]
      ]
    ]);

    // Register for posts
    register_graphql_field('WPPageInfo', 'total', [
      'type' => 'Int',
      'description' => 'Total number of posts',
    ]);

    register_graphql_field('RootQueryToContentNodeConnectionWhereArgs', 'termIds', [
      'type' => ['list_of' => 'Int'], // Accept an array of integers
      'description' => __('Filter by post objects that have specific term IDs across multiple taxonomies', 'tofino'),
    ]);
  }

  public function add_offset_ordering($values)
  {
    if (!isset($values['OFFSET'])) {
      $values['OFFSET'] = [
        'value' => 'offset',
        'description' => __('Order by offset', 'tofino'),
      ];
    }

    return $values;
  }

  public function add_offset_pagination($fields, $typename)
  {
    if ($typename === 'RootQueryToContentNodeConnectionWhereArgs') {
      $fields['offsetPagination'] = [
        'type' => ['list_of' => 'Int'],
        'description' => __('Offset pagination for posts', 'tofino'),
      ];
    }

    return $fields;
  }

  public function add_query_args($query_args, $source, $args)
  {
    $offsetPagination = $args['where']['offsetPagination'] ?? null;

    if (is_array($offsetPagination) && count($offsetPagination) === 2) {
      [$offset, $per_page] = $offsetPagination;

      $query_args['offset'] = (int) $offset;
      $query_args['posts_per_page'] = (int) $per_page;
    }

    return $query_args;
  }

  function filter_post_by_term_ids($args, $source, $query_args, $context, $info)
  {
    // Log the query arguments for debugging
    // error_log('GraphQL Query Args Before: ' . print_r($args, true));

    // Ensure WP_Query calculates the total number of posts
    $args['no_found_rows'] = false;

    // Check if 'termIds' exists in the 'where' argument
    if (array_key_exists('where', $query_args) && array_key_exists('termIds', $query_args['where'])) {
      $term_ids = $query_args['where']['termIds'];

      if (!empty($term_ids) && is_array($term_ids)) {
        $taxonomy_terms = [];

        foreach ($term_ids as $term_id) {
          // Get the taxonomy for the term ID
          $term = get_term($term_id);

          if ($term && !is_wp_error($term)) {
            $taxonomy = $term->taxonomy;

            // Group term IDs by taxonomy
            if (!isset($taxonomy_terms[$taxonomy])) {
              $taxonomy_terms[$taxonomy] = [];
            }

            $taxonomy_terms[$taxonomy][] = $term_id;
          }
        }

        // Build the tax_query
        $tax_query = [];

        foreach ($taxonomy_terms as $taxonomy => $terms) {
          $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field' => 'term_id',
            'terms' => $terms,
            'operator' => 'IN',
          ];
        }

        if (!empty($tax_query)) {
          // If there are multiple taxonomies, set the relation to 'AND'
          $args['tax_query'] = [
            'relation' => 'AND',
            ...$tax_query,
          ];
        }
      }
    }

    // Log the final query arguments for debugging
    // error_log('GraphQL Query Args After: ' . print_r($args, true));

    return $args;
  }

  public function update_page_info($page_info, $connection)
  {
    // Ensure the query is an instance of WP_Query
    if ($connection->get_query() instanceof \WP_Query) {
      $query = $connection->get_query();

      // Check if the query is using offsetPagination
      $offset = $query->query_vars['offset'] ?? null;
      $posts_per_page = $query->query_vars['posts_per_page'] ?? null;
      $total_posts = $query->found_posts;
      $page_info['total'] = $total_posts;

      if ($offset !== null && $posts_per_page !== null) {
        // Calculate hasNextPage and hasPreviousPage
        $page_info['hasNextPage'] = ($offset + $posts_per_page) < $total_posts;
        $page_info['hasPreviousPage'] = $offset > 0;
      } else {
      }
    }

    return $page_info;
  }
}

new GraphQL();
