document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('#carouselExample');
    const items = carousel.querySelectorAll('.carousel-item');
    const prevBtn = carousel.querySelector('.carousel-control-prev');
    const nextBtn = carousel.querySelector('.carousel-control-next');
    let currentIndex = 0;
    let autoSlideInterval;

    // Fungsi untuk menampilkan slide
    function showSlide(index) {
        items.forEach((item, i) => {
            item.classList.remove('active');
            item.style.opacity = '0';
            item.style.transform = 'translateX(100%)';
        });
        items[index].classList.add('active');
        items[index].style.opacity = '1';
        items[index].style.transform = 'translateX(0)';
    }

    // Fungsi untuk slide berikutnya
    function nextSlide() {
        currentIndex = (currentIndex + 1) % items.length;
        showSlide(currentIndex);
    }

    // Fungsi untuk slide sebelumnya
    function prevSlide() {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        showSlide(currentIndex);
    }

    // Event listeners untuk tombol
    nextBtn.addEventListener('click', function(e) {
        e.preventDefault();
        nextSlide();
        resetAutoSlide();
    });

    prevBtn.addEventListener('click', function(e) {
        e.preventDefault();
        prevSlide();
        resetAutoSlide();
    });

    // Auto-slide setiap 4 detik
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 4000);
    }

    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // Inisialisasi
    showSlide(currentIndex);
    startAutoSlide();

    // Tambahkan efek hover pada gambar
    items.forEach(item => {
        const img = item.querySelector('img');
        img.addEventListener('mouseenter', () => {
            img.style.transform = 'scale(1.1)';
            img.style.transition = 'transform 0.3s ease';
        });
        img.addEventListener('mouseleave', () => {
            img.style.transform = 'scale(1)';
        });
    });
});