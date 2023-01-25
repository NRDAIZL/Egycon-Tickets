
@php
    
    $progress = Session::has('progress') ? Session::get('progress') : 0;
    $progress = $progress > 100 ? 100 : $progress;
    $progress = $progress < 0 ? 0 : $progress;
    $progress = round($progress);
    $progress_n = $progress;
    $progress = $progress.'%';
    echo '<script>
    parent.document.getElementById("progressbar").innerHTML="<div style=\"width:'.$progress.';background:linear-gradient(to bottom, rgba(125,126,125,1) 0%,rgba(14,14,14,1) 100%); ;height:35px;\">&nbsp;</div>";
    parent.document.getElementById("progressbar").dataset.progress="'.$progress_n.'";
    parent.document.getElementById("information").innerHTML="<div style=\"text-align:center; font-weight:bold\">'.$progress.' is processed.</div>";</script>';
    if($progress == '100%'){
        echo '<script>parent.document.getElementById("information").innerHTML="<div style=\"text-align:center; font-weight:bold\">Process completed</div>"</script>';
    }
    ob_flush(); 
    flush();
    Session::forget('progress'); 
@endphp