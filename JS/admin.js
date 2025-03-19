// Function to approve a post
function approvePost(questionId) {
  fetch('admin_approve_post.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({
          question_id: questionId,
      }),
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert('Post approved successfully!');
          location.reload(); // Refresh the page
      } else {
          alert('Failed to approve post.');
      }
  })
  .catch(error => {
      console.error('Error:', error);
  });
}

// Function to reject a post
function rejectPost(questionId) {
  fetch('admin_reject_post.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({
          question_id: questionId,
      }),
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert('Post rejected successfully!');
          location.reload(); // Refresh the page
      } else {
          alert('Failed to reject post.');
      }
  })
  .catch(error => {
      console.error('Error:', error);
  });
}

// Function to delete a post (question)
function deletePost(questionId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch('admin_delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question_id: questionId,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post deleted successfully!');
                location.reload(); // Refresh the page
            } else {
                alert('Failed to delete post.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// Function to delete an answer
function deleteAnswer(answerId) {
    if (confirm('Are you sure you want to delete this answer?')) {
        fetch('admin_delete_answer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                answer_id: answerId,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Answer deleted successfully!');
                location.reload(); // Refresh the page
            } else {
                alert('Failed to delete answer.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// Function to ignore a report
function ignoreReport(reportId) {
    if (confirm('Are you sure you want to ignore this report?')) {
        fetch('admin_ignore_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                report_id: reportId,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report ignored successfully!');
                // Refresh the reported posts section
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to ignore report.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// Function to open the full question modal
function openFullQuestionModal(title, username, time, content, photos) {
    const modal = document.getElementById('fullQuestionModal');
    const modalTitle = document.getElementById('modalQuestionTitle');
    const modalUsername = document.getElementById('modalQuestionUsername');
    const modalTime = document.getElementById('modalQuestionTime');
    const modalContent = document.getElementById('modalQuestionContent');
    const modalPhotos = document.getElementById('modalQuestionPhotos');

    // Set modal content
    modalTitle.textContent = title;
    modalUsername.textContent = username; // Set username once
    modalTime.textContent = time; // Set time once
    modalContent.textContent = content;

    // Clear previous photos
    modalPhotos.innerHTML = '';

    // Add photos to the modal
    photos.forEach(photo => {
        const img = document.createElement('img');
        img.src = photo.photo_path;
        img.alt = 'Question Photo';
        modalPhotos.appendChild(img);
    });

    // Display the modal
    modal.style.display = 'block';
}

// Function to close the modal
function closeFullQuestionModal() {
    const modal = document.getElementById('fullQuestionModal');
    modal.style.display = 'none';
}

// Close the modal when the close button is clicked
document.querySelector('.close').addEventListener('click', closeFullQuestionModal);

// Close the modal when clicking outside the modal content
window.addEventListener('click', (event) => {
    const modal = document.getElementById('fullQuestionModal');
    if (event.target === modal) {
        closeFullQuestionModal();
    }
});

// Add click event to pending and reported posts
document.querySelectorAll('.pending-posts .post, .reported-posts .post').forEach(post => {
    post.addEventListener('click', () => {
        const title = post.querySelector('h3').textContent;
        const username = post.querySelector('.username') ? post.querySelector('.username').textContent : 'Unknown';
        const time = post.querySelector('.time-ago') ? post.querySelector('.time-ago').textContent : 'Unknown';
        const content = post.querySelector('p').textContent;
        const photos = Array.from(post.querySelectorAll('.question-photo')).map(img => ({
            photo_path: img.src
        }));

        openFullQuestionModal(title, username, time, content, photos);
    });
});