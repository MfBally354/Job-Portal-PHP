// File JavaScript untuk interaktivitas tambahan
document.addEventListener('DOMContentLoaded', function() {
    console.log('JobPortal loaded successfully!');
    
    // Animasi smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
