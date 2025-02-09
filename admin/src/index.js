const { render } = wp.element; // React's render method

import App from './App';

const rootElement = document.getElementById('pricedropnotifexpert-root');

if (rootElement) {
    render(<App />, rootElement); // JSX rendering
}
