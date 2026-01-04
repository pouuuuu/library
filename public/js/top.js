document.addEventListener('DOMContentLoaded', () => {
    const rankingList = document.getElementById('ranking-list');
    const paginationContainer = document.getElementById('pagination');
    const loadingMessage = document.getElementById('loading-message');
    const filterBtns = document.querySelectorAll('.filter-btn');

    let currentSort = new URLSearchParams(window.location.search).get('sort') || 'rating';

    if (!rankingList) return;

    async function loadResources(page = 1) {
        if (loadingMessage) loadingMessage.style.display = 'block';
        rankingList.style.opacity = '0.5';

        const params = new URLSearchParams();
        params.append('route', 'top');
        params.append('action', 'list');
        params.append('limit', '20');
        params.append('page', page.toString());
        params.append('sort', currentSort);

        const url = window.location.pathname + '?' + params.toString();

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Erreur réseau');
            const data = await response.json();

            if (loadingMessage) loadingMessage.style.display = 'none';
            rankingList.style.opacity = '1';

            if (!data.success) {
                console.error(data.error);
                return;
            }

            displayRanking(data.resources, data.startRank, currentSort);
            updatePagination(data.totalPages, data.currentPage);

            window.history.pushState({}, '', url);

        } catch (error) {
            console.error('Erreur:', error);
            if (loadingMessage) loadingMessage.style.display = 'none';
            rankingList.style.opacity = '1';
        }
    }

    function displayRanking(resources, startRank, sortType) {
        rankingList.innerHTML = '';

        if (!resources || resources.length === 0) {
            rankingList.innerHTML = '<p style="text-align:center; color:#888;">Aucun livre trouvé.</p>';
            return;
        }

        resources.forEach((resource, index) => {
            const rank = startRank + index;
            const item = document.createElement('a');
            item.href = `?book=${resource.id}`;
            item.className = `ranking-item rank-${rank}`;

            const posterUrl = resource.poster || '/public/img/default-cover.png';

            let scoreHtml = '';
            // GESTION DU TRI COMMENTAIRES
            if (sortType === 'comments') {
                scoreHtml = `${resource.comment_count} <small>Avis</small>`;
            } else {
                scoreHtml = `★ ${parseFloat(resource.rating).toFixed(1)} <small>Moyenne</small>`;
            }

            item.innerHTML = `
                <div class="rank-number">#${rank}</div>
                <img src="${escapeHtml(posterUrl)}" alt="${escapeHtml(resource.title)}" class="rank-poster" onerror="this.src='/public/img/default-cover.png'">
                <div class="rank-info">
                    <div class="rank-title">${escapeHtml(resource.title)}</div>
                </div>
                <div class="rank-score">
                    ${scoreHtml}
                </div>
            `;
            rankingList.appendChild(item);
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            currentSort = btn.dataset.sort;
            loadResources(1);
        });
    });

    function updatePagination(totalPages, currentPage) {
        if (!paginationContainer) return;
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }
        let html = '';
        if (currentPage > 1) {
            html += `<a href="#" data-page="${currentPage - 1}" class="pagination-button">← Précédent</a>`;
        } else {
            html += `<span class="pagination-button disabled">← Précédent</span>`;
        }
        html += `<span style="margin: 0 10px;">Page ${currentPage} / ${totalPages}</span>`;
        if (currentPage < totalPages) {
            html += `<a href="#" data-page="${currentPage + 1}" class="pagination-button">Suivant →</a>`;
        } else {
            html += `<span class="pagination-button disabled">Suivant →</span>`;
        }
        paginationContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    if (paginationContainer) {
        paginationContainer.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.dataset.page) {
                e.preventDefault();
                loadResources(parseInt(link.dataset.page));
                const header = document.querySelector('.ranking-header');
                if (header) header.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
});