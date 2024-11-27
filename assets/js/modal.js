document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('trailer-modal');
    const modalVideo = document.getElementById('trailer-video');
    const modalTitle = document.getElementById('modal-title');
    const modalVotes = document.getElementById('modal-votes');
    const modalStars = document.getElementById('modal-stars');
    const modalOverview = document.getElementById('modal-overview');
    const closeBtn = document.getElementsByClassName('close')[0];

    function openModal(movieId) {
        fetch(`/movie/${movieId}`)
            .then(response => response.json())
            .then(data => {
                modalTitle.textContent = data.title;
                modalVotes.textContent = `(${data.vote_count} votes)`;
                modalOverview.textContent = data.overview;

                // Mise à jour des étoiles
                const rating = data.vote_average / 2;
                modalStars.innerHTML = '';
                for (let i = 1; i <= 5; i++) {
                    const star = document.createElement('i');
                    star.classList.add('fas');
                    if (i <= Math.floor(rating)) {
                        star.classList.add('fa-star');
                    } else if (i - 0.5 <= rating) {
                        star.classList.add('fa-star-half-alt');
                    } else {
                        star.classList.add('fa-star');
                        star.style.color = '#e4e5e9';
                    }
                    modalStars.appendChild(star);
                }

                if (data.trailer) {
                    modalVideo.src = data.trailer;
                    modalVideo.style.display = 'block';
                } else {
                    modalVideo.style.display = 'none';
                    const noTrailerMessage = document.createElement('p');
                    noTrailerMessage.textContent = "Désolé, aucune bande-annonce n'est disponible pour ce film.";
                    modalVideo.parentNode.insertBefore(noTrailerMessage, modalVideo);
                }

                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors du chargement des détails du film.');
            });
    }

    function closeModal() {
        modal.style.display = 'none';
        modalVideo.src = '';
        const noTrailerMessage = modalVideo.previousElementSibling;
        if (noTrailerMessage && noTrailerMessage.tagName === 'P') {
            noTrailerMessage.remove();
        }
    }

    // Ouvrir le modal pour le lecteur vidéo principal
    document.querySelector('.video-player').addEventListener('click', function() {
        openModal(this.dataset.movieId);
    });

    // Ouvrir le modal pour les cartes de films
    document.querySelectorAll('.movie-card').forEach(function(card) {
        card.addEventListener('click', function() {
            openModal(this.dataset.movieId);
        });
    });

    // Fermer le modal
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            closeModal();
        }
    });
});