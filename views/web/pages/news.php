<?php $pageTitle = 'ข่าวสาร'; ?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-newspaper me-2"></i>ข่าวสาร</h2>
        <div>
            <input type="text" id="searchNews" class="form-control" placeholder="ค้นหาข่าว..." style="width:250px">
        </div>
    </div>

    <div id="newsContainer" class="row g-4">
        <div class="text-center py-5">
            <span class="spinner-border text-primary"></span>
            <p class="mt-2 text-muted">กำลังโหลดข่าวสาร...</p>
        </div>
    </div>

    <nav id="newsPagination" class="mt-4"></nav>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(function () {
    let currentPage = 1;
    const perPage = 12;

    async function loadNews(page = 1) {
        currentPage = page;
        const container = $('#newsContainer');
        container.html('<div class="col-12 text-center py-5"><span class="spinner-border text-primary"></span></div>');

        const params = { page, per_page: perPage };
        const search = $('#searchNews').val().trim();
        if (search) params.search = search;

        const result = await API.getNewsList(params);

        if (!result.success || !result.data || result.data.length === 0) {
            container.html('<div class="col-12 text-center py-5"><p class="text-muted">ไม่พบข่าวสาร</p></div>');
            $('#newsPagination').empty();
            return;
        }

        let html = '';
        result.data.forEach(news => {
            const img = news.cover_image ? App.imgUrl(news.cover_image) : './assets/images/default-news.jpg';
            const date = App.formatDate(news.published_at || news.created_at);
            const excerpt = news.excerpt || (news.content ? news.content.substring(0, 120) + '...' : '');

            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm news-card" onclick="location.href='./?page=news-detail&id=${news.id}'" style="cursor:pointer">
                    <img src="${img}" class="card-img-top" style="height:200px;object-fit:cover"
                        alt="${news.title}" onerror="App.defaultImage(this,'news')">
                    <div class="card-body">
                        <h5 class="card-title">${news.title}</h5>
                        <p class="card-text text-muted small">${excerpt}</p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>${date}
                            <i class="bi bi-eye ms-2 me-1"></i>${App.formatNumber(news.views || 0)}
                        </small>
                    </div>
                </div>
            </div>`;
        });

        container.html(html);

        // Pagination
        if (result.pagination) {
            App.buildPagination('#newsPagination', result.pagination, loadNews);
        }
    }

    loadNews();

    // Search with debounce
    let searchTimer;
    $('#searchNews').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadNews(1), 400);
    });
});
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
