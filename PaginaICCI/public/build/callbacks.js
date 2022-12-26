function showImages(id, imgCallback){
    if (!id) return;

    let imgData = document.getElementById(id);
    imgData.src = URL.createObjectURL(event.target.files[0]);

    imgCallback(imgData);
}