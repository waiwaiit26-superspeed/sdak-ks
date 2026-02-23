<?php $pageTitle = 'รายละเอียดข่าว'; ?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<div class="container py-5">
    <div id="newsDetail">
        <div class="text-center py-5">
            <span class="spinner-border text-primary"></span>
            <p class="mt-2 text-muted">กำลังโหลดข่าว...</p>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const newsId = urlParams.get('id');
    if (!newsId) {
        window.location.href = './?page=news';
        return;
    }

    async function loadNewsDetail() {
        const result = await API.getNewsDetail(newsId);
        const container = $('#newsDetail');

        if (!result.success || !result.data) {
            container.html(`
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">ไม่พบข่าวที่ต้องการ</p>
                    <a href="./?page=news" class="btn btn-outline-primary">กลับไปหน้าข่าวสาร</a>
                </div>
            `);
            return;
        }

        const n = result.data;
        const date = App.formatDateTime(n.published_at || n.created_at);
        const coverImg = n.cover_image ? `<img src="${App.imgUrl(n.cover_image)}" class="img-fluid rounded mb-4" alt="${n.title}" onerror="this.style.display='none'">` : '';

        container.html(`
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../">หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="./?page=news">ข่าวสาร</a></li>
                    <li class="breadcrumb-item active">${n.title}</li>
                </ol>
            </nav>
            <article>
                <h1 class="mb-3">${n.title}</h1>
                <div class="d-flex align-items-center text-muted mb-4">
                    <small>
                        <i class="bi bi-person me-1"></i>${n.author_name || 'Admin'}
                        <span class="mx-2">|</span>
                        <i class="bi bi-calendar me-1"></i>${date}
                        <span class="mx-2">|</span>
                        <i class="bi bi-eye me-1"></i>${App.formatNumber(n.views || 0)} ครั้ง
                    </small>
                </div>
                ${coverImg}
                <div class="news-content">${n.content}</div>
            </article>
            <hr>
            <div class="d-flex justify-content-between">
                <a href="./?page=news" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับไปหน้าข่าวสาร
                </a>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="navigator.share?.({title:'${n.title}',url:location.href})">
                        <i class="bi bi-share me-1"></i> แชร์
                    </button>
                </div>
            </div>
        `);

        document.title = n.title + ' - ส.ร.ม.ก.';
    }

    loadNewsDetail();
});
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
