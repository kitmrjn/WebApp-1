// Function to handle question click
document.querySelectorAll('.question').forEach(question => {
    question.addEventListener('click', function(event) {
        event.stopPropagation(); // Prevent event from bubbling up
  
        const questionId = this.getAttribute('data-question-id');
        const preview = this.querySelector('.answer-preview');
        const fullContent = this.querySelector('.answer-full');
  
        // Toggle visibility of preview and full content
        if (preview.style.display !== 'none') {
            preview.style.display = 'none';
            fullContent.style.display = 'block';
        } else {
            preview.style.display = 'block';
            fullContent.style.display = 'none';
        }
  
        // Ensure the Answer button is always visible
        const answerButton = this.querySelector('.answer-button');
        answerButton.style.display = 'block';
  
        // Remove all other questions (optional, based on your design)
        document.querySelectorAll('.question').forEach(q => {
            if (q !== this) {
                q.style.display = 'none'; // Hide other questions
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
  
  // Close expanded question when clicking outside
  document.addEventListener('click', function(event) {
      if (!event.target.closest('.question')) {
          document.querySelectorAll('.question').forEach(question => {
              const preview = question.querySelector('.answer-preview');
              const fullContent = question.querySelector('.answer-full');
              preview.style.display = 'block';
              fullContent.style.display = 'none';
              question.classList.remove('expanded');
              question.style.display = 'block'; // Show all questions again
          });
      }
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
  
  // Profile dropdown functionality
  const profileDropdown = document.querySelector('.profile-dropdown');
  const dropdownContent = document.querySelector('.dropdown-content');
  
  // Toggle dropdown on profile icon click
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