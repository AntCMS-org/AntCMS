class TinyZoom {
    constructor(selector) {
        document.querySelectorAll(selector).forEach(image => {
            image.style.cursor = 'pointer';
            image.addEventListener('click', () => this.makeFullscreen(image));
        });
    }

    makeFullscreen(image) {
        const fullscreen = document.createElement('div');
        fullscreen.classList.add("fullscreen-image");
        document.body.appendChild(fullscreen);

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        let scaleFactor = 1;
        canvas.width = image.width;
        canvas.height = image.height;

        ctx.drawImage(image, 0, 0);

        fullscreen.appendChild(canvas);

        function zoom(scale) {
            scaleFactor *= scale;
            canvas.style.transform = `scale(${scaleFactor})`;
        }

        const viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        const viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

        const initialscaleWidth = (viewportWidth * 0.85) / image.width;
        const initialscaleHeight = (viewportHeight * 0.85) / image.height;

        let overScale = Math.min(initialscaleWidth, initialscaleHeight);
        if (overScale != 1) {
            scaleFactor = overScale;
        }
        zoom(1);

        canvas.style.position = 'fixed';
        canvas.style.left = `${viewportWidth / 2 - image.width / 2}px`;
        canvas.style.top = `${viewportHeight / 2 - image.height / 2}px`;
        canvas.style.cursor = 'move';

        let dragStartX, dragStartY, dragged = false;

        canvas.addEventListener('mousedown', (event) => {
            event.preventDefault()
            dragStartX = event.pageX - canvas.offsetLeft;
            dragStartY = event.pageY - canvas.offsetTop;
            dragged = true;
        });

        canvas.addEventListener('mousemove', (event) => {
            if (dragged) {
                const x = event.pageX - dragStartX;
                const y = event.pageY - dragStartY;
                canvas.style.left = `${x}px`;
                canvas.style.top = `${y}px`;
            }
        });

        canvas.addEventListener('mouseup', () => {
            dragged = false;
        });

        canvas.addEventListener('wheel', (event) => {
            event.preventDefault();
            zoom(event.deltaY > 0 ? 0.9 : 1.1);
        });

        canvas.addEventListener('dblclick', (event) => {
            event.preventDefault();
            zoom(event.shiftKey ? 0.5 : 2);
        });

        fullscreen.addEventListener('click', (event) => {
            if (event.target === fullscreen) {
                fullscreen.remove();
            }
        });

        fullscreen.addEventListener('wheel', (event) => event.preventDefault());
    }
}

window.addEventListener("load", () => {
    new TinyZoom(".TinyZoom");
});