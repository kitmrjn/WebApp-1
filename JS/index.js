// Function to handle question click
document.querySelectorAll('.question').forEach(question => {
  question.addEventListener('click', function(event) {
      event.stopPropagation(); // Prevent event from bubbling up

      const questionId = this.getAttribute('data-question-id');
      const questionTitle = this.querySelector('h3').innerText;
      const questionUsername = this.querySelector('.username').innerText;
      const questionTime = this.querySelector('.time-ago').innerText;
      const questionContent = this.querySelector('.answer-full').innerHTML;
      const questionPhoto = this.querySelector('.question-photo-thumbnail')?.outerHTML || '';

      // Populate the modal with question details
      document.getElementById('modalQuestionTitle').innerText = questionTitle;
      document.getElementById('modalQuestionUsername').innerText = questionUsername;
      document.getElementById('modalQuestionTime').innerText = questionTime;
      document.getElementById('modalQuestionContent').innerHTML = questionContent;
      document.getElementById('modalQuestionPhoto').innerHTML = questionPhoto;

      // Show the modal
      document.getElementById('fullQuestionModal').style.display = 'block';
  });
});

// Close modals when clicking the close button
document.querySelectorAll('.close').forEach(closeButton => {
  closeButton.addEventListener('click', function() {
      document.getElementById('fullQuestionModal').style.display = 'none';
      document.getElementById('answerModal').style.display = 'none';
  });
});

// Close modals when clicking outside
window.onclick = function(event) {
  const fullQuestionModal = document.getElementById('fullQuestionModal');
  const answerModal = document.getElementById('answerModal');
  if (event.target == fullQuestionModal) {
      fullQuestionModal.style.display = 'none';
  }
  if (event.target == answerModal) {
      answerModal.style.display = 'none';
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