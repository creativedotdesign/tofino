<?php

/**
 * GraphQL
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

class GraphQL
{
  /**
   * Constructor. Registers WPGraphQL filters when the plugin is active.
   *
   * Bails silently if WPGraphQL is not installed, so the theme does not
   * hard-depend on the plugin being present.
   */
  public function __construct()
  {
    if (!class_exists('WPGraphQL')) {
      return;
    }

    add_filter('graphql_register_types', [$this, 'register']);
    add_filter('graphql_PostObjectsConnectionOrderbyEnum_values', [$this, 'add_offset_ordering']);
    add_filter('graphql_input_fields', [$this, 'add_offset_pagination'], 10, 2);
    add_filter('graphql_post_object_connection_query_args', [$this, 'add_query_args'], 10, 5);
    add_filter('graphql_post_object_connection_query_args', [$this, 'filter_post_by_term_ids'], 10, 5);
    add_filter('graphql_connection_page_info', [$this, 'update_page_info'], 10, 2);
  }

  /**
   * Registers custom GraphQL types and fields.
   *
   * - Registers the `OffsetPagination` object type.
   * - Adds a `total` field to `WPPageInfo`.
   * - Adds a `termIds` where argument to `RootQueryToContentNodeConnectionWhereArgs`.
   *
   * @return void
   */
  public function register(): void
  {
    register_graphql_object_type('OffsetPagination', [
      'fields' => [
        'total' => [
          'type'        => 'Int',
          'description' => 'Total number of items',
        ],
      ],
    ]);

    register_graphql_field('WPPageInfo', 'total', [
      'type'        => 'Int',
      'description' => 'Total number of posts',
    ]);

    register_graphql_field('RootQueryToContentNodeConnectionWhereArgs', 'termIds', [
      'type'        => ['list_of' => 'Int'],
      'description' => __('Filter by post objects that have specific term IDs across multiple taxonomies', 'tofino'),
    ]);
  }

  /**
   * Adds an OFFSET enum value to the GraphQL post ordering enum.
   *
   * @param array<string, mixed> $values Existing enum values.
   * @return array<string, mixed> Updated enum values including OFFSET.
   */
  public function add_offset_ordering(array $values): array
  {
    if (!isset($values['OFFSET'])) {
      $values['OFFSET'] = [
        'value'       => 'offset',
        'description' => __('Order by offset', 'tofino'),
      ];
    }

    return $values;
  }

  /**
   * Adds an `offsetPagination` input field to content node connection where args.
   *
   * @param array<string, mixed> $fields   Existing input fields.
   * @param string               $typename The GraphQL type name being processed.
   * @return array<string, mixed> Updated input fields.
   */
  public function add_offset_pagination(array $fields, string $typename): array
  {
    if ($typename === 'RootQueryToContentNodeConnectionWhereArgs') {
      $fields['offsetPagination'] = [
        'type'        => ['list_of' => 'Int'],
        'description' => __('Offset pagination for posts', 'tofino'),
      ];
    }

    return $fields;
  }

  /**
   * Applies offset pagination to the WP_Query args when `offsetPagination`
   * is passed in the GraphQL where clause as a [offset, perPage] tuple.
   *
   * @param array<string, mixed> $query_args WP_Query arguments to modify.
   * @param mixed                $_source    The source object being queried (unused).
   * @param array<string, mixed> $args       GraphQL query arguments, including `where`.
   * @param mixed                $_context   The GraphQL app context (unused).
   * @param mixed                $_info      The GraphQL resolve info (unused).
   * @return array<string, mixed> Modified WP_Query arguments.
   */
  public function add_query_args(
    array $query_args,
    mixed $_source,
    array $args,
    mixed $_context,
    mixed $_info
  ): array {
    $offset_pagination = $args['where']['offsetPagination'] ?? null;

    if (is_array($offset_pagination) && count($offset_pagination) === 2) {
      [$offset, $per_page] = $offset_pagination;

      $query_args['offset']         = (int) $offset;
      $query_args['posts_per_page'] = (int) $per_page;
    }

    return $query_args;
  }

  /**
   * Adds a `tax_query` to the WP_Query args when `termIds` are passed in the
   * GraphQL where clause. Term IDs are grouped by taxonomy, and an AND relation
   * is applied when multiple taxonomies are involved.
   *
   * Also ensures `no_found_rows` is false so WP_Query calculates totals.
   *
   * @param array<string, mixed> $query_args WP_Query arguments to modify.
   * @param mixed                $_source    The source object being queried (unused).
   * @param array<string, mixed> $args       GraphQL query arguments, including `where`.
   * @param mixed                $_context   The GraphQL app context (unused).
   * @param mixed                $_info      The GraphQL resolve info (unused).
   * @return array<string, mixed> Modified WP_Query arguments.
   */
  public function filter_post_by_term_ids(
    array $query_args,
    mixed $_source,
    array $args,
    mixed $_context,
    mixed $_info
  ): array {
    $query_args['no_found_rows'] = false;

    $term_ids = $args['where']['termIds'] ?? null;

    if (empty($term_ids) || !is_array($term_ids)) {
      return $query_args;
    }

    $taxonomy_terms = [];

    foreach ($term_ids as $term_id) {
      $term = get_term($term_id);

      if ($term instanceof \WP_Term) {
        $taxonomy_terms[$term->taxonomy][] = $term_id;
      }
    }

    if (empty($taxonomy_terms)) {
      return $query_args;
    }

    $tax_query = [];

    foreach ($taxonomy_terms as $taxonomy => $terms) {
      $tax_query[] = [
        'taxonomy' => $taxonomy,
        'field'    => 'term_id',
        'terms'    => $terms,
        'operator' => 'IN',
      ];
    }

    $query_args['tax_query'] = count($tax_query) > 1
      ? ['relation' => 'AND', ...$tax_query]
      : $tax_query;

    return $query_args;
  }

  /**
   * Updates the GraphQL page info with the total post count and recalculates
   * `hasNextPage` and `hasPreviousPage` when offset pagination is in use.
   *
   * @param array<string, mixed> $page_info  The current page info array.
   * @param mixed                $connection The GraphQL connection instance.
   * @return array<string, mixed> Updated page info.
   */
  public function update_page_info(array $page_info, mixed $connection): array
  {
    if (!($connection->get_query() instanceof \WP_Query)) {
      return $page_info;
    }

    $query       = $connection->get_query();
    $total_posts = $query->found_posts;
    $offset      = $query->query_vars['offset'] ?? null;
    $per_page    = $query->query_vars['posts_per_page'] ?? null;

    $page_info['total'] = $total_posts;

    if ($offset !== null && $per_page !== null) {
      $page_info['hasNextPage']     = ($offset + $per_page) < $total_posts;
      $page_info['hasPreviousPage'] = $offset > 0;
    }

    return $page_info;
  }
}

new GraphQL();
