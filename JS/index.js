// Function to open the reply modal
function openReplyModal(parentId) {
  document.getElementById('modalParentId').value = parentId;
  document.getElementById('answerModal').style.display = 'block';
}

// Function to handle question click
document.querySelectorAll('.question').forEach(question => {
  question.addEventListener('click', function() {
      const questionId = this.getAttribute('data-question-id');

      // Remove all other questions
      document.querySelectorAll('.question').forEach(q => {
          if (q !== this) {
              q.remove(); // Remove the question from the DOM
          }
      });

      // Check if answers are already loaded
      const answersDiv = this.querySelector('.answers');
      if (answersDiv) {
          // If answers are already loaded, just expand the question
          this.classList.add('expanded');
          return; // Exit the function
      }

      // Fetch and display answers
      fetch(`get_answers.php?question_id=${questionId}`)
          .then(response => response.text())
          .then(data => {
              // Create a new div to display the answers
              const answersDiv = document.createElement('div');
              answersDiv.className = 'answers';
              answersDiv.innerHTML = data;

              // Append answers to the expanded question
              this.appendChild(answersDiv);

              // Expand the question smoothly
              this.classList.add('expanded');
          })
          .catch(error => console.error('Error fetching answers:', error));
  });
});

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

// Modal handling
function openAnswerModal(questionId) {
  document.getElementById('modalQuestionId').value = questionId;
  document.getElementById('answerModal').style.display = 'block';
}

document.querySelector('.close').addEventListener('click', function() {
  document.getElementById('answerModal').style.display = 'none';
});

window.onclick = function(event) {
  const modal = document.getElementById('answerModal');
  if (event.target == modal) {
      modal.style.display = 'none';
  }
}