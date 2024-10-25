import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client';

// HTTP connection to the API
const httpLink = createHttpLink({
  // You should use an absolute URL here
  uri: '/' + tofinoJS.graphqlEndpoint,
});

// Cache implementation
const cache = new InMemoryCache();

// Create the apollo client
export const apolloClient = new ApolloClient({
  link: httpLink,
  cache,
});

export default apolloClient;
