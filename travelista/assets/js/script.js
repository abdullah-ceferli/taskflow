function navBarLinksChilds() {
    document.querySelectorAll('.menu-has-children').forEach(function (menu) {
        let timeout
        const submenu = menu.querySelector('ul')

        menu.addEventListener('mouseenter', function () {
            clearTimeout(timeout)
            submenu.classList.add('is-visible')
        })

        menu.addEventListener('mouseleave', function () {
            timeout = setTimeout(function () {
                submenu.classList.remove('is-visible')
            }, 200)
        })
    })
}

function heroSectionMenuBar() {
    document.addEventListener('DOMContentLoaded', function () {
        const tabLinks = document.querySelectorAll('.tab-link')
        const tabPanes = document.querySelectorAll('.tab-pane')

        tabLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault()

                tabLinks.forEach(item => item.classList.remove('active'))

                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active')
                })

                this.classList.add('active');

                const targetId = this.getAttribute('href')
                const targetPane = document.querySelector(targetId);

                targetPane.classList.add('active')
                setTimeout(() => {
                    targetPane.classList.add('show')
                }, 10)
            })
        })
    })
}

function navBarMovement() {
    const headerSticky = document.getElementById("nav-bar")
    const navBarMainMenu = document.querySelector(".nav-bar-main-menu")

    window.addEventListener("scroll", () => {
        if (window.scrollY > 80) {
            headerSticky.classList.add("nav-bar-scrolled")
            navBarMainMenu.style.background = "transparent"
        }
        else {
            headerSticky.classList.remove("nav-bar-scrolled")
            navBarMainMenu.style.background = "rgba(255, 255, 255, 0.15)"
        }
    })
}

function testimonialCarusel() {
    const slider = document.getElementById('slider')
    const dotContainer = document.getElementById('dotContainer')
    const dots = dotContainer.querySelectorAll('.testimonial-nav-dot')
    const cards = slider.querySelectorAll('.testimonial-card')
    let autoSlideInterval

    const totalItems = dots.length; 

    function goToSlide(index) {
        const cardWidth = cards[0].offsetWidth + 20
        slider.scrollTo({
            left: cardWidth * index,
            behavior: 'smooth'
        });
        resetAutoSlide()
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index)
        })
    })

    slider.addEventListener('scroll', () => {
        const cardWidth = cards[0].offsetWidth + 20
        const scrollPos = slider.scrollLeft
        
        const activeIndex = Math.round(scrollPos / cardWidth) % totalItems

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === activeIndex)
        })
    })

    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            const cardWidth = cards[0].offsetWidth + 20
            let currentIndex = Math.round(slider.scrollLeft / cardWidth)
            
            let nextIndex = (currentIndex + 1) % totalItems

            if (currentIndex >= totalItems) {
                slider.scrollTo({ left: 0, behavior: 'auto' })
                nextIndex = 1
            }

            goToSlide(nextIndex)
        }, 10000)
    }

    function resetAutoSlide() {
        clearInterval(autoSlideInterval)
        startAutoSlide()
    }
    
    startAutoSlide()
}

function recentBlogCarusel() {
    const slider = document.getElementById('blogSlider')
    const dots = document.querySelectorAll('.recent-blog-dot')
    const cards = document.querySelectorAll('.single-recent-blog-post:not(.clone)')
    let autoPlay

    const getWidth = () => cards[0].offsetWidth + 30

    const scrollToIndex = (i) => {
        slider.scrollTo({ left: getWidth() * i, behavior: 'smooth' })
    }

    const startAuto = () => {
        autoPlay = setInterval(() => {
            let current = Math.round(slider.scrollLeft / getWidth())
            let next = (current + 1) % 4

            if (current >= 3) {
                slider.scrollTo({ left: 0, behavior: 'auto' })
                next = 1;
            }
            scrollToIndex(next)
        }, 10000)
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            clearInterval(autoPlay)
            scrollToIndex(i)
            setTimeout(startAuto, 3000)
        })
    })

    slider.addEventListener('scroll', () => {
        const activeIdx = Math.round(slider.scrollLeft / getWidth()) % 4
        dots.forEach((d, i) => d.classList.toggle('active', i === activeIdx))
    })

    startAuto()

}

function smoothUp() {
    document.querySelectorAll('a[href="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault()
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            })
        })
    })
}


navBarLinksChilds()

heroSectionMenuBar()

navBarMovement()

testimonialCarusel()

recentBlogCarusel()

smoothUp()