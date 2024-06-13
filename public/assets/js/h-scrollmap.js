const container = document.getElementById('sortable');
const scrollRange = document.getElementById('scrollRange');
const customScroll = document.getElementById('customScroll');

function updateThumbWidth() {
    const containerWidth = container.scrollWidth;
    const containerClientWidth = container.clientWidth;
    const thumbWidth = (containerClientWidth / containerWidth) * 100 + "%";
    document.documentElement.style.setProperty('--thumb-width', thumbWidth);
}

function checkOverflow() {
    if (container.scrollWidth > container.clientWidth) {
        customScroll.style.display = 'block';
    } else {
        customScroll.style.display = 'none';
    }
}

scrollRange.addEventListener('input', () => {
    const maxScrollLeft = container.scrollWidth - container.clientWidth;
    const scrollPercentage = scrollRange.value / 100;
    container.scrollLeft = scrollPercentage * maxScrollLeft;
});

container.addEventListener('scroll', () => {
    const maxScrollLeft = container.scrollWidth - container.clientWidth;
    const scrollPercentage = (container.scrollLeft / maxScrollLeft) * 100;
    scrollRange.value = scrollPercentage;
});

updateThumbWidth();
checkOverflow();

window.addEventListener('resize', () => {
    updateThumbWidth();
    checkOverflow();
});