<?php
/**
 * Auth Template — Footer (AdminLTE 3)
 * Closes body + html
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
<div class="text-center mt-3 mb-3">
    <small class="text-muted">Dev by <a href="https://www.facebook.com/kroowaiwai" target="_blank" rel="noopener">Waiwai jaidee</a></small>
</div>
<?php include __DIR__ . '/../_shared/pdpa.php'; ?>

<!-- Histats.com  (div with counter) --><div id="histats_counter"></div>
<!-- Histats.com  START  (aync)-->
<script type="text/javascript">var _Hasync= _Hasync|| [];
_Hasync.push(['Histats.start', '1,5009244,4,436,112,75,00011111']);
_Hasync.push(['Histats.fasi', '1']);
_Hasync.push(['Histats.track_hits', '']);
(function() {
var hs = document.createElement('script'); hs.type = 'text/javascript'; hs.async = true;
hs.src = ('//s10.histats.com/js15_as.js');
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(hs);
})();</script>
<noscript><a href="/" target="_blank"><img  src="//sstatic1.histats.com/0.gif?5009244&101" alt="counter hit make" border="0"></a></noscript>
<!-- Histats.com  END  -->
</body>
</html>
