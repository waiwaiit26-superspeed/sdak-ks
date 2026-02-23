<?php
/**
 * Member Template — Footer (AdminLTE 3)
 * Closes content-wrapper, renders main-footer, closes wrapper + body + html
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
        </div><!-- /.container -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer text-center">
        <div><!-- Histats.com  (div with counter) --><div id="histats_counter" style="display:inline-block"></div></div>
        <div class="mt-1">
            <strong>&copy; <?php echo date('Y') + 543; ?> ส.ร.ม.ก. กาฬสินธุ์</strong> สงวนลิขสิทธิ์
            <span class="d-none d-sm-inline ml-2">| Dev by <a href="https://www.facebook.com/kroowaiwai" target="_blank" rel="noopener">Waiwai jaidee</a></span>
        </div>
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
    </footer>
</div><!-- ./wrapper -->

<?php include __DIR__ . '/../_shared/pdpa.php'; ?>
</body>
</html>
