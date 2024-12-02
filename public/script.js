document.addEventListener('DOMContentLoaded', function() { 
    function date_time() {
        const now = new Date();
        const current_time = now.toLocaleTimeString();
        const current_date = now.toLocaleDateString();
        document.getElementById("time").innerHTML = current_time;
        document.getElementById("date").innerHTML = current_date;
    }

    function updateFontSize() {
        const selectedSize = document.getElementById("font-size").value;
        const inputs = document.querySelectorAll("main input[type='text']");
        const labels = document.querySelectorAll("main label");
        const subheaders = document.querySelectorAll("main h4");
        const headers = document.querySelectorAll("main h2");
        const textareas = document.querySelectorAll("main textarea");
        const ps = document.querySelectorAll("main p");

        inputs.forEach(input => {
            input.style.fontSize = selectedSize;
        });
        labels.forEach(label => {
            label.style.fontSize = selectedSize;
        });

        subheaders.forEach(subheader => {
            subheader.style.fontSize = selectedSize;
        });

        headers.forEach(header => {
            header.style.fontSize = (parseInt(selectedSize) + 8) + "px";
        });

        textareas.forEach(textarea => {
            textarea.style.fontSize = selectedSize;
        });

        ps.forEach(p => {
            p.style.fontSize = selectedSize;
        });
    }
    
    date_time();
    setInterval(date_time, 1000);
    
    const fontSize = document.getElementById("font-size");
    const backgroundColor = document.getElementById("bg-color");
    
    fontSize.addEventListener('change', function() {
        updateFontSize();
    });
    
    backgroundColor.addEventListener('change', function() {
        document.body.style.backgroundColor = this.value;
    });
});
