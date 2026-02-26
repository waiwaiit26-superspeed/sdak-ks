<?php
$pageTitle = 'หน้าเพจ';
$currentPage = 'dynamic';
$slug = $_GET['slug'] ?? '';

$extraCss = '
<style>
.page-content { font-size: 1.05rem; line-height: 1.8; }
.page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
.page-content h2, .page-content h3 { margin-top: 1.5rem; margin-bottom: 0.75rem; }
.page-content p { margin-bottom: 1rem; }
.page-content ul, .page-content ol { margin-bottom: 1rem; padding-left: 1.5rem; }
</style>';
?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<div class="container py-5">
    <div id="dynamicLoading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 text-muted">กำลังโหลด...</p>
    </div>

    <div id="dynamicContent" style="display:none;">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $basePath ?>">หน้าแรก</a></li>
                <li class="breadcrumb-item active" id="breadcrumbTitle"></li>
            </ol>
        </nav>

        <div id="pageCover" class="mb-4" style="display:none;">
            <img id="pageCoverImg" src="" alt="" class="img-fluid rounded shadow-sm w-100" style="max-height:400px;object-fit:cover;">
        </div>

        <h1 class="mb-4 fw-bold" id="pageTitle"></h1>
        <div id="pageBody" class="page-content"></div>
    </div>

    <div id="dynamicNotFound" style="display:none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
        <h3 class="mt-3">ไม่พบหน้าเพจ</h3>
        <p class="text-muted">หน้าเพจที่คุณต้องการอาจถูกลบหรือไม่มีอยู่</p>
        <a href="<?= $basePath ?>" class="btn btn-primary mt-2"><i class="bi bi-house me-1"></i> กลับหน้าแรก</a>
    </div>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
const pageSlug = '<?= addslashes($slug) ?>';

$(function() {
    if (!pageSlug) {
        showNotFound();
        return;
    }
    loadDynamicPage();
});

async function loadDynamicPage() {
    try {
        const res = await API.getPageDetail(pageSlug);
        if (res.success && res.data) {
            const pg = res.data;
            document.title = pg.title + ' | <?php echo SITE_NAME_SHORT; ?>';
            $('#pageTitle').text(pg.title);
            $('#breadcrumbTitle').text(pg.title);

            // Process shortcodes [news], [activities], etc.
            let content = pg.content || '<p class="text-muted">ยังไม่มีเนื้อหา</p>';
            if (typeof Modules !== 'undefined') {
                content = Modules.parse(content);
            }
            $('#pageBody').html(content);

            // Render module shortcodes after DOM insert
            if (typeof Modules !== 'undefined') {
                await Modules.renderPending();
            }

            if (pg.cover_image) {
                $('#pageCoverImg').attr('src', App.imgUrl(pg.cover_image)).attr('alt', pg.title);
                $('#pageCover').show();
            }

            $('#dynamicLoading').hide();
            $('#dynamicContent').show();
        } else {
            showNotFound();
        }
    } catch (e) {
        showNotFound();
    }
}

function showNotFound() {
    $('#dynamicLoading').hide();
    $('#dynamicNotFound').show();
}
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
