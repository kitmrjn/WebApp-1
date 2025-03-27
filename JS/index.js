document.querySelectorAll('.question').forEach(question => {
    question.addEventListener('click', function(event) {
        if (event.target.classList.contains('answer-button') || event.target.classList.contains('report-button')) {
            return;
        }

        event.stopPropagation();

        const questionId = this.getAttribute('data-question-id');
        const questionTitle = this.querySelector('h3').innerText;
        const questionUsername = this.querySelector('.username').innerText;
        const questionTime = this.querySelector('.time-ago').innerText;
        const questionContent = this.querySelector('.answer-full').innerHTML;

        fetch(`get_photos.php?question_id=${questionId}`)
            .then(response => response.text())
            .then(photos => {
                document.getElementById('modalQuestionTitle').innerText = questionTitle;
                document.getElementById('modalQuestionUsername').innerText = questionUsername;
                document.getElementById('modalQuestionTime').innerText = questionTime;
                document.getElementById('modalQuestionContent').innerHTML = questionContent;
                document.getElementById('modalQuestionPhoto').innerHTML = photos;

                const modal = document.getElementById('fullQuestionModal');
                modal.style.display = 'block';

                document.body.style.overflow = 'hidden';

                modal.querySelector('.close').addEventListener('click', function() {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; 
                });

                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto'; 
                    }
                };
            });
    });
});


document.querySelectorAll('.close').forEach(closeButton => {
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            const modal = this.closest('.modal'); 
            modal.style.display = 'none'; 
            document.body.style.overflow = 'auto'; 
        });
    }
});

window.onclick = function(event) {
    const fullQuestionModal = document.getElementById('fullQuestionModal');
    const answerModal = document.getElementById('answerModal');
    if (event.target == fullQuestionModal) {
        fullQuestionModal.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }
    if (event.target == answerModal) {
        answerModal.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }
}

function searchQuestions() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let questions = document.getElementsByClassName("question");

    for (let i = 0; i < questions.length; i++) {
        let title = questions[i].querySelector("h3").innerText.toLowerCase();
        let content = questions[i].querySelector(".answer-preview").innerText.toLowerCase();

        if (title.includes(input) || content.includes(input)) {
            questions[i].style.display = "block";
        } else {
            questions[i].style.display = "none";
        }
    }
}

function expandSearchBar() {
    if (window.matchMedia("(max-width: 768px)").matches) {
        const searchBar = document.querySelector('.search-bar');
        const searchInput = document.getElementById('searchInput');

        if (searchBar && searchInput) {
            searchBar.style.maxWidth = '100%'; 
            searchInput.style.width = '100%'; 
            searchInput.style.opacity = '1'; 
            searchInput.style.padding = '8px 35px 8px 12px'; 
            searchInput.focus(); 
        }
    }
}

function collapseSearchBar() {
    if (window.matchMedia("(max-width: 768px)").matches) {
        const searchBar = document.querySelector('.search-bar');
        const searchInput = document.getElementById('searchInput');

        if (searchBar && searchInput) {
            searchBar.style.maxWidth = '50px'; 
            searchInput.style.width = '0'; 
            searchInput.style.opacity = '0'; 
            searchInput.style.padding = '8px 0'; 
            searchInput.value = ''; 
        }
    }
}

function openAnswerModal(questionId) {
    document.getElementById('modalQuestionId').value = questionId;
    document.getElementById('answerModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; 
}

document.querySelectorAll('.report-button').forEach(reportButton => {
    if (reportButton) {
        reportButton.addEventListener('click', function(event) {
            event.stopPropagation();
            const questionId = this.closest('.question').getAttribute('data-question-id');
            alert(`Report question with ID: ${questionId}`);
        });
    }
});

const profileDropdown = document.querySelector('.profile-dropdown');
const dropdownContent = document.querySelector('.dropdown-content');

if (profileDropdown && dropdownContent) {
    profileDropdown.addEventListener('click', function(event) {
        event.stopPropagation(); 
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function(event) {
        if (!profileDropdown.contains(event.target)) {
            dropdownContent.style.display = 'none';
        }
    });
}

document.querySelectorAll('.answer-rating').forEach(rating => {
    const starIcon = rating.querySelector('i');
    const ratingCount = rating.querySelector('.rating-count');
    const answerId = rating.closest('.answer-box').getAttribute('data-answer-id');
    const isHelpful = starIcon.getAttribute('data-is-helpful') === 'true';
    if (isHelpful) {
        starIcon.classList.add('selected');
    }

    rating.addEventListener('click', function () {
        const isSelected = starIcon.classList.toggle('selected');
        const newIsHelpful = isSelected ? 1 : 0;

        let count = parseInt(ratingCount.textContent);
        count = isSelected ? count + 1 : count - 1;
        ratingCount.textContent = count;

        fetch('rate_answer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                answer_id: answerId,
                is_helpful: newIsHelpful,
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Rating saved successfully!');
                } else {
                    console.error('Failed to save rating.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});

function reportPost(questionId) {
    const reason = prompt("Why are you reporting this post?");
    if (reason) {
        fetch('report_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question_id: questionId,
                reason: reason,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post reported successfully!');
        
                const reportButton = document.querySelector(`.report-button[data-question-id="${questionId}"]`);
                if (reportButton) {
                    reportButton.classList.add('reported');
                }
            } else {
                alert(data.message || 'Failed to report post.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

function reportAnswer(answerId) {
    const reason = prompt("Why are you reporting this answer?");
    if (reason) {
        fetch('report_answer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                answer_id: answerId,
                reason: reason,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Answer reported successfully!');
                const flagIcon = document.querySelector(`.answer-report[data-answer-id="${answerId}"] i`);
                if (flagIcon) {
                    flagIcon.classList.add('reported');
                }
            } else {
                alert(data.message || 'Failed to report answer.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

function toggleMenu() {
    const navMenu = document.getElementById('nav-menu');
    navMenu.classList.toggle('active');
}

document.addEventListener('click', function(event) {
    const navMenu = document.getElementById('nav-menu');
    const hamburger = document.querySelector('.hamburger');

    if (!navMenu.contains(event.target) && !hamburger.contains(event.target)) {
        navMenu.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchIcon = document.querySelector('.search-icon');

    if (searchInput) {
        searchInput.addEventListener('keyup', searchQuestions);

        document.addEventListener('click', function(event) {
            const searchBar = document.querySelector('.search-bar');
            if (!searchBar.contains(event.target)) {
                collapseSearchBar(); 
            }
        });

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                collapseSearchBar(); 
            }
        });
    }

    if (searchIcon) {
        searchIcon.addEventListener('click', function(event) {
            event.stopPropagation(); 
            expandSearchBar(); 
        });
    }
});

