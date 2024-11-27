import './styles/app.scss';
import './js/modal.js';

document.addEventListener('DOMContentLoaded', function() {
    // Movie Rating System
    const ratingSystem = {
        initMainPageRating(container) {
            const stars = container.querySelectorAll('.user-star');
            const movieId = container.closest('.movie-card').dataset.movieId;

            stars.forEach(star => {
                star.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const rating = this.dataset.rating;
                    ratingSystem.updateStars(container, rating);
                    console.log(`Vous avez noté le film (ID: ${movieId}) ${rating}/5 étoiles !`);
                    // TODO: Add AJAX request to send rating to server
                });
            });
        },

        updateStars(container, rating) {
            container.querySelectorAll('.user-star').forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                    star.style.color = '#ffc107'; // Yellow for active stars
                } else {
                    star.classList.remove('active');
                    star.style.color = '#e4e5e9'; // Grey for inactive stars
                }
            });
        },

        initAllRatings() {
            document.querySelectorAll('.movie-card .user-rating').forEach(this.initMainPageRating);
        }
    };

    // Modal Management
    const modalManager = {
        modal: document.getElementById('trailer-modal'),
        modalVideo: document.getElementById('trailer-video'),
        modalTitle: document.getElementById('modal-title'),
        modalVotes: document.getElementById('modal-votes'),
        modalStars: document.getElementById('modal-stars'),
        modalOverview: document.getElementById('modal-overview'),
        modalUserRating: document.getElementById('modal-user-rating'),
        closeBtn: document.getElementsByClassName('close')[0],

        openModal(movieId) {
            fetch(`/movie/${movieId}`)
                .then(response => response.json())
                .then(data => {
                    this.populateModalContent(data);
                    this.handleTrailerDisplay(data.trailerUrl);
                    this.initModalRating(movieId);
                    this.modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors du chargement des détails du film.');
                });
        },

        populateModalContent(data) {
            this.modalTitle.textContent = data.title;
            this.modalVotes.textContent = `(${data.voteCount} votes)`;
            this.modalOverview.textContent = data.overview;
            this.updateModalStars(data.voteAverage / 2);
        },

        handleTrailerDisplay(trailerUrl) {
            const videoContainer = this.modalVideo.parentNode;
            if (trailerUrl) {
                this.modalVideo.src = trailerUrl;
                this.modalVideo.style.display = 'block';
                videoContainer.querySelector('p')?.remove();
            } else {
                this.modalVideo.style.display = 'none';
                if (!videoContainer.querySelector('p')) {
                    const noTrailerMessage = document.createElement('p');
                    noTrailerMessage.textContent = "Désolé, aucune bande-annonce n'est disponible pour ce film.";
                    videoContainer.insertBefore(noTrailerMessage, this.modalVideo);
                }
            }
        },

        updateModalStars(rating) {
            this.modalStars.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.classList.add('fas');
                if (i <= Math.floor(rating)) {
                    star.classList.add('fa-star');
                    star.style.color = '#ffc107'; // Yellow for full stars
                } else if (i - 0.5 <= rating) {
                    star.classList.add('fa-star-half-alt');
                    star.style.color = '#ffc107'; // Yellow for half stars
                } else {
                    star.classList.add('fa-star');
                    star.style.color = '#e4e5e9'; // Grey for empty stars
                }
                this.modalStars.appendChild(star);
            }
        },

        initModalRating(movieId) {
            const stars = this.modalUserRating.querySelectorAll('.user-star');

            stars.forEach(star => {
                star.removeEventListener('click', star.ratingClickHandler);
                star.ratingClickHandler = () => {
                    const rating = star.dataset.rating;
                    ratingSystem.updateStars(this.modalUserRating, rating);
                    console.log(`Vous avez noté le film (ID: ${movieId}) ${rating}/5 étoiles !`);
                    // TODO: Add AJAX request to send rating to server
                };
                star.addEventListener('click', star.ratingClickHandler);
            });

            ratingSystem.updateStars(this.modalUserRating, 0);
        },

        closeModal() {
            this.modal.style.display = 'none';
            this.modalVideo.src = '';
            this.modalVideo.previousElementSibling?.remove();
        },

        initModalListeners() {
            document.querySelector('.video-player').addEventListener('click', () => this.openModal(this.dataset.movieId));
            document.querySelectorAll('.movie-card').forEach(card => {
                card.addEventListener('click', function(event) {
                    if (!event.target.closest('.user-rating')) {
                        modalManager.openModal(this.dataset.movieId);
                    }
                });
            });
            this.closeBtn.addEventListener('click', () => this.closeModal());
            window.addEventListener('click', (event) => {
                if (event.target == this.modal) {
                    this.closeModal();
                }
            });
        }
    };

    // Genre Filtering
    const genreFilter = {
        genreForm: document.getElementById('genre-form'),
        genreInputs: document.querySelectorAll('#genre-form input[name="genre"]'),
        searchInput: document.getElementById('search-input'),
        resetButton: document.getElementById('reset-filters'),

        init() {
            this.genreInputs.forEach(input => {
                input.addEventListener('change', () => {
                    if (this.searchInput.value) {
                        const searchField = document.createElement('input');
                        searchField.type = 'hidden';
                        searchField.name = 'search';
                        searchField.value = this.searchInput.value;
                        this.genreForm.appendChild(searchField);
                    }
                    this.genreForm.submit();
                });
            });

            this.resetButton.addEventListener('click', () => {
                this.searchInput.value = '';
                const allGenreRadio = document.querySelector('input[name="genre"][value="all"]');
                if (allGenreRadio) {
                    allGenreRadio.checked = true;
                }
                this.genreForm.submit();
            });
        }
    };

    // Search Functionality
    const searchManager = {
        searchForm: document.getElementById('search-form'),
        searchInput: document.getElementById('search-input'),
        autocompleteResults: document.getElementById('autocomplete-results'),
        debounceTimer: null,

        init() {
            this.searchForm.addEventListener('submit', this.handleSearchSubmit.bind(this));
            this.searchInput.addEventListener('input', this.handleSearchInput.bind(this));
            document.addEventListener('click', this.handleClickOutside.bind(this));
        },

        handleSearchSubmit(e) {
            e.preventDefault();
            const selectedGenre = document.querySelector('input[name="genre"]:checked');
            if (selectedGenre) {
                const genreField = document.createElement('input');
                genreField.type = 'hidden';
                genreField.name = 'genre';
                genreField.value = selectedGenre.value;
                this.searchForm.appendChild(genreField);
            }
            this.searchForm.submit();
        },

        handleSearchInput() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                const query = this.searchInput.value;
                if (query.length > 2) {
                    this.fetchAutocompleteResults(query);
                } else {
                    this.autocompleteResults.classList.add('hidden');
                }
            }, 300);
        },

        fetchAutocompleteResults(query) {
            fetch(`/autocomplete?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    this.displayAutocompleteResults(data);
                });
        },

        displayAutocompleteResults(data) {
            this.autocompleteResults.innerHTML = '';
            data.forEach(movie => {
                const div = document.createElement('div');
                div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                div.textContent = `${movie.title} (${movie.year})`;
                div.addEventListener('click', () => {
                    this.searchInput.value = movie.title;
                    this.autocompleteResults.classList.add('hidden');
                    this.searchForm.submit();
                });
                this.autocompleteResults.appendChild(div);
            });
            this.autocompleteResults.classList.remove('hidden');
        },

        handleClickOutside(event) {
            if (!this.searchInput.contains(event.target) && !this.autocompleteResults.contains(event.target)) {
                this.autocompleteResults.classList.add('hidden');
            }
        }
    };

    // Initialize all components
    ratingSystem.initAllRatings();
    modalManager.initModalListeners();
    genreFilter.init();
    searchManager.init();
});