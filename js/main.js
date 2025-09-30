const burger = document.getElementById('burgerBtn');
const sideMenu = document.getElementById('sideMenu');
const closeBtn = document.getElementById('closeMenu');

burger.addEventListener('click', () => {
  sideMenu.classList.toggle('open');
});

closeBtn.addEventListener('click', () => {
  sideMenu.classList.remove('open');
});

window.addEventListener('load', () => {
  const splash = document.getElementById('splash');
  const mainContent = document.getElementById('main-content');
  const mainHeader = document.getElementById('mainHeader');

  if (!splash) {
    console.warn('Splash element (#splash) not found.');
    if (mainContent) mainContent.classList.add('show');
    if (mainHeader) mainHeader.classList.add('show');
    return;
  }

  // How long the splash stays visible (ms)
  const VISIBLE_MS = 2500;
  // How long the CSS transition for outro lasts (ms) - should match CSS 0.8s
  const OUTRO_MS = 800;

  setTimeout(() => {
    // Start the outro transition
    splash.classList.add('outro');

    // Wait for the CSS transition to finish, then remove/hide the splash and reveal main
    const onTransitionEnd = (e) => {
      // ensure we respond to opacity/transform on the splash itself
      if (e.target !== splash) return;
      splash.removeEventListener('transitionend', onTransitionEnd);

      // hide completely so it doesn't capture clicks or remain in tab order
      splash.style.display = 'none';
      // show the main content and header
      if (mainContent) mainContent.classList.add('show');
      if (mainHeader) mainHeader.classList.add('show');
    };

    splash.addEventListener('transitionend', onTransitionEnd);

    // Fallback in case transitionend doesn't fire for any reason
    setTimeout(() => {
      if (splash.style.display !== 'none') {
        splash.style.display = 'none';
        if (mainContent) mainContent.classList.add('show');
        if (mainHeader) mainHeader.classList.add('show');
      }
    }, OUTRO_MS + 100);

  }, VISIBLE_MS);
});
