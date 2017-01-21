<script>
var UploadMovieFilePreview = new Image();

function previewFile() {
    if(document.getElementById('UploadMovieFilePreview') !== null){
        elem = document.getElementById('UploadMovieFilePreview');
        elem.parentNode.removeChild(elem);
    }else{
        UploadMovieFilePreview = new Image();
        UploadMovieFilePreview.id = 'UploadMovieFilePreview';
        //document.body.append(UploadMovieFilePreview);
    }
    if(SnapshotCamFile[0]['name'] == ""){
        var file    = document.getElementById('UploadMovieFile').files[0];
        
        var reader  = new FileReader();

        reader.addEventListener("load", function () {
          UploadMovieFilePreview.src = reader.result;
          init_snapshotcam(false,true,false,false);
        }, false);

        if (file) {
          reader.readAsDataURL(file);
        }
    }else{
        UploadMovieFilePreview.onerror = function(e) {
            UploadMovieFilePreview.src = 'Missing.jpg';
        }        
        UploadMovieFilePreview.onload = function () {
            init_snapshotcam(false,false,true,false);
            UploadMovieFilePreview.onload = '';
        }
        UploadMovieFilePreview.src   = SnapshotCamFile[0]['name'];
    }
}
</script>