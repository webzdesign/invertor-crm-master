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

function updateScrollPosition() {
    const maxScrollLeft = container.scrollWidth - container.clientWidth;
    const scrollPercentage = scrollRange.value / 100;
    container.scrollLeft = scrollPercentage * maxScrollLeft;
}

scrollRange.addEventListener('input', updateScrollPosition);

container.addEventListener('scroll', () => {
    const maxScrollLeft = container.scrollWidth - container.clientWidth;
    const scrollPercentage = (container.scrollLeft / maxScrollLeft) * 100;
    scrollRange.value = scrollPercentage;
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        const step = 5; // Define how much to move per key press (in percentage)
        let newValue = parseFloat(scrollRange.value);

        if (e.key === 'ArrowLeft') {
            newValue = Math.max(0, newValue - step);
        } else if (e.key === 'ArrowRight') {
            newValue = Math.min(100, newValue + step);
        }

        scrollRange.value = newValue;
        updateScrollPosition();

        e.preventDefault();
    }
});

scrollRange.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
        e.preventDefault();
    }
});

updateThumbWidth();
checkOverflow();

window.addEventListener('resize', () => {
    updateThumbWidth();
    checkOverflow();
});