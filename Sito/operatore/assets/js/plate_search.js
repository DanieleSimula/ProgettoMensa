document.addEventListener("DOMContentLoaded", function () {
  // Recupero la variabile da PHP
  const activeSection = "<?php echo $active_section; ?>";

  if (activeSection === "piatti") {
    // Cerco il link della sidebar che apre i piatti e lo clicco automaticamente
    const linkPiatti = document.querySelector(
      '.sidebar a[data-section="piatti"]',
    );
    if (linkPiatti) {
      linkPiatti.click();
    }
  }
});
