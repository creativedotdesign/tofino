import { mount } from '@cypress/vue';
import MyNewComponent from './MyNewComponent.vue'; // keep the .vue, Vite needs it
// import '../css/main.css';

describe('MyNewComponent', () => {
  it('renders', () => {
    mount(MyNewComponent).get('h1').should('have.text', 'Hello world');
  });
  it('renders', () => {
    mount(MyNewComponent).get('p').should('contain', 'Testing');
  });
});
