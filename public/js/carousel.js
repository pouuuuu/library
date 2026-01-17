document.addEventListener('DOMContentLoaded', () => {
    document.querySelector("#carousel-left-button").addEventListener("click", e => {
        scrollCarousel(-1);
    })

    document.querySelector("#carousel-right-button").addEventListener("click", e => {
        scrollCarousel(1);
    })
})

function scrollCarousel(direction) {
    const container = document.querySelector('#carousel > div');
    const item = container.querySelector('.resource');

    if (!item) return;

    const gap = 15;
    const scrollAmount = item.offsetWidth + gap;

    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}