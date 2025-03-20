function authorization(){
	window.location.href="/";
}

document.addEventListener("DOMContentLoaded", function() {
    const notification = document.querySelector('.notification');
    if (notification) {
        setTimeout(() => {
            notification.style.opacity = '0'; 
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000); 
    }
});
