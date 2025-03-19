// Function to handle question click
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
      fetch(`get_photos.php?question_id=${questionId}`)
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

// Search functionality
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

// Function to report a post
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