//Function to handle question click
document.querySelectorAll('.question').forEach(question => {
    question.addEventListener('click', function(event) {
        // Prevent the event from triggering if the click is on the "View Answers" button
        if (event.target.classList.contains('answer-button') || event.target.classList.contains('report-button')) {
            return; // Allow the default behavior (navigation to question.php or report action)
        }

        event.stopPropagation(); // Prevent event from bubbling up

        const questionId = this.getAttribute('data-question-id');
        const questionTitle = this.querySelector('h3').innerText;
        const questionUsername = this.querySelector('.username').innerText;
        const questionTime = this.querySelector('.time-ago').innerText;
        const questionContent = this.querySelector('.answer-full').innerHTML;

        // Fetch all photos for the question
        fetch(`/webapp/questions/get_photos.php?question_id=${questionId}`)
            .then(response => response.text())
            .then(photos => {
                // Populate the modal with question details
                document.getElementById('modalQuestionTitle').innerText = questionTitle;
                document.getElementById('modalQuestionUsername').innerText = questionUsername;
                document.getElementById('modalQuestionTime').innerText = questionTime;
                document.getElementById('modalQuestionContent').innerHTML = questionContent;
                document.getElementById('modalQuestionPhoto').innerHTML = photos;

                // Show the modal
                const modal = document.getElementById('fullQuestionModal');
                modal.style.display = 'block';

                // Prevent background scrolling
                document.body.style.overflow = 'hidden';

                // Re-enable background scrolling when the modal is closed
                modal.querySelector('.close').addEventListener('click', function() {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; // Re-enable background scrolling
                });

                // Re-enable background scrolling when clicking outside the modal
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto'; // Re-enable background scrolling
                    }
                };
            });
    });
});

// Close modals when clicking the close button
document.querySelectorAll('.close').forEach(closeButton => {
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            const modal = this.closest('.modal'); // Find the closest modal
            modal.style.display = 'none'; // Hide the modal
            document.body.style.overflow = 'auto'; // Re-enable background scrolling
        });
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const fullQuestionModal = document.getElementById('fullQuestionModal');
    const answerModal = document.getElementById('answerModal');
    if (event.target == fullQuestionModal) {
        fullQuestionModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable background scrolling
    }
    if (event.target == answerModal) {
        answerModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable background scrolling
    }
}

// Search functionality with AJAX
function searchQuestions() {
    let input = document.getElementById("searchInput").value.trim();
    
    // If search input is empty, reload the current page to show all questions
    if (input === '') {
        window.location.reload();
        return;
    }
    
    // Show loading indicator
    document.getElementById('questionsContainer').innerHTML = '<div class="loading">Searching...</div>';
    
    // Make AJAX request to search endpoint
    fetch('/webapp/includes/search_questions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'searchTerm=' + encodeURIComponent(input)
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('questionsContainer').innerHTML = html;
        // Hide pagination when showing search results
        document.querySelector('.pagination').style.display = 'none';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('questionsContainer').innerHTML = '<div class="error">Error loading search results</div>';
    });
}



// Expand search bar on smaller screens
function expandSearchBar() {
    if (window.matchMedia("(max-width: 768px)").matches) {
        const searchBar = document.querySelector('.search-bar');
        const searchInput = document.getElementById('searchInput');

        if (searchBar && searchInput) {
            searchBar.style.maxWidth = '100%'; // Expand the search bar
            searchInput.style.width = '100%'; // Expand the input field
            searchInput.style.opacity = '1'; // Show the input field
            searchInput.style.padding = '8px 35px 8px 12px'; // Restore padding
            searchInput.focus(); // Focus on the input field
        }
    }
}

// Collapse search bar on smaller screens
function collapseSearchBar() {
    if (window.matchMedia("(max-width: 768px)").matches) {
        const searchBar = document.querySelector('.search-bar');
        const searchInput = document.getElementById('searchInput');

        if (searchBar && searchInput) {
            searchBar.style.maxWidth = '50px'; // Collapse the search bar
            searchInput.style.width = '0'; // Hide the input field
            searchInput.style.opacity = '0'; // Hide the input field
            searchInput.style.padding = '8px 0'; // Remove padding
            searchInput.value = ''; // Clear the input field
        }
    }
}

// Answer Modal handling
function openAnswerModal(questionId) {
    document.getElementById('modalQuestionId').value = questionId;
    document.getElementById('answerModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

// Report button functionality
document.querySelectorAll('.report-button').forEach(reportButton => {
    if (reportButton) {
        reportButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the question click event from firing
            const questionId = this.closest('.question').getAttribute('data-question-id');
            alert(`Report question with ID: ${questionId}`); // Replace with actual report functionality
        });
    }
});

// Profile dropdown functionality
const profileDropdown = document.querySelector('.profile-dropdown');
const dropdownContent = document.querySelector('.dropdown-content');

// Toggle dropdown on profile icon click
if (profileDropdown && dropdownContent) {
    profileDropdown.addEventListener('click', function(event) {
        event.stopPropagation(); // Prevent the click from bubbling up
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!profileDropdown.contains(event.target)) {
            dropdownContent.style.display = 'none';
        }
    });
}

// Function to handle star rating
document.querySelectorAll('.answer-rating').forEach(rating => {
    const starIcon = rating.querySelector('i');
    const ratingCount = rating.querySelector('.rating-count');
    const answerId = rating.closest('.answer-box').getAttribute('data-answer-id');

    // Load the star's initial state
    const isHelpful = starIcon.getAttribute('data-is-helpful') === 'true';
    if (isHelpful) {
        starIcon.classList.add('selected');
    }

    // Add click event listener
    rating.addEventListener('click', function () {
        const isSelected = starIcon.classList.toggle('selected');
        const newIsHelpful = isSelected ? 1 : 0;

        // Update the count
        let count = parseInt(ratingCount.textContent);
        count = isSelected ? count + 1 : count - 1;
        ratingCount.textContent = count;

        // Send the rating to the backend
        fetch('/webapp/questions/rate_answer.php', {
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

// Function to report a post
function reportPost(questionId) {
    const reason = prompt("Why are you reporting this post?");
    if (reason) {
        fetch('/webapp/questions/report_post.php', {
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
                // Add a 'reported' class to the report button
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

// Function to report an answer
function reportAnswer(answerId) {
    const reason = prompt("Why are you reporting this answer?");
    if (reason) {
        fetch('/webapp/questions/report_answer.php', {
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
                // Add a 'reported' class to the flag icon
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

// Toggle menu for smaller screens
function toggleMenu() {
    const navMenu = document.getElementById('nav-menu');
    navMenu.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const navMenu = document.getElementById('nav-menu');
    const hamburger = document.querySelector('.hamburger');

    // Close nav menu if clicking outside
    if (!navMenu.contains(event.target) && !hamburger.contains(event.target)) {
        navMenu.classList.remove('active');
    }
});

// Ensure the search bar works on page load
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchIcon = document.querySelector('.search-icon');

    if (searchInput) {
        searchInput.addEventListener('keyup', searchQuestions);

        // Collapse search bar when clicking outside (only on smaller screens)
        document.addEventListener('click', function(event) {
            const searchBar = document.querySelector('.search-bar');
            if (!searchBar.contains(event.target)) {
                collapseSearchBar(); // Collapse the search bar
            }
        });

        // Collapse search bar when pressing "Escape" key (only on smaller screens)
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                collapseSearchBar(); // Collapse the search bar
            }
        });
    }

    if (searchIcon) {
        searchIcon.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the click from bubbling up
            expandSearchBar(); // Expand the search bar on smaller screens
        });
    }
});

document.getElementById('searchInput').addEventListener('focus', function() {
    if (window.innerWidth <= 768) { // Check if viewport width is 768px or below
        document.getElementById('ask-text').style.display = 'none';
    }
});

document.getElementById('searchInput').addEventListener('blur', function() {
    if (window.innerWidth <= 768) { // Check if viewport width is 768px or below
        document.getElementById('ask-text').style.display = 'block';
    }
});