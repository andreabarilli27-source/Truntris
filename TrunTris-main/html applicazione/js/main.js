// Transizioni smooth per i link
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Loading state per i bottoni
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if(submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Caricamento...';
            submitBtn.disabled = true;
        }
    });
});