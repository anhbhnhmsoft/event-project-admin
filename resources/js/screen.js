import Swiper from 'swiper/bundle';
window.Swiper = Swiper;

new Swiper('.eventSwiper', {
    direction: 'horizontal',
    slidesPerView: 1,
    spaceBetween: 0,
    loop: false,
    mousewheel: true,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});
