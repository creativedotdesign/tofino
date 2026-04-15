import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core';

let client: ApolloClient<unknown> | null = null;

/**
 * Returns the shared Apollo Client instance, creating it on first call.
 * Throws if WPGraphQL is not active (graphqlEndpoint not set in tofinoJS).
 *
 * @returns The Apollo Client instance.
 */
export const getApolloClient = (): ApolloClient<unknown> => {
  if (client) {
    return client;
  }

  if (!tofinoJS.graphqlEndpoint) {
    throw new Error('tofinoJS.graphqlEndpoint is not defined. Is WPGraphQL active?');
  }

  client = new ApolloClient({
    link: createHttpLink({ uri: '/' + tofinoJS.graphqlEndpoint }),
    cache: new InMemoryCache(),
  });

  return client;
};
