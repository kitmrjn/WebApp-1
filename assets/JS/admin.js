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
            location.reload(); 
        } else {
            alert('Failed to approve post.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
  }
  
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
            location.reload(); 
        } else {
            alert('Failed to reject post.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
  }
  
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
                  location.reload(); 
              } else {
                  alert('Failed to delete post.');
              }
          })
          .catch(error => {
              console.error('Error:', error);
          });
      }
  }
  
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
                  location.reload(); 
              } else {
                  alert('Failed to delete answer.');
              }
          })
          .catch(error => {
              console.error('Error:', error);
          });
      }
  }
  
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
                  location.reload(); 
              } else {
                  alert('Failed to ignore report.');
              }
          })
          .catch(error => {
              console.error('Error:', error);
          });
      }
  }
  
  function openFullQuestionModal(title, username, time, content, photos) {
      const modal = document.getElementById('fullQuestionModal');
      const modalTitle = document.getElementById('modalQuestionTitle');
      const modalUsername = document.getElementById('modalQuestionUsername');
      const modalTime = document.getElementById('modalQuestionTime');
      const modalContent = document.getElementById('modalQuestionContent');
      const modalPhotos = document.getElementById('modalQuestionPhotos');

      modalTitle.textContent = title;
      modalUsername.textContent = username; 
      modalTime.textContent = time; 
      modalContent.textContent = content;
  
      modalPhotos.innerHTML = '';
  
      photos.forEach(photo => {
          const img = document.createElement('img');
          img.src = photo.photo_path;
          img.alt = 'Question Photo';
          modalPhotos.appendChild(img);
      });
  
      modal.style.display = 'block';
  }
  
  function closeFullQuestionModal() {
      const modal = document.getElementById('fullQuestionModal');
      modal.style.display = 'none';
  }
  
  document.querySelector('.close').addEventListener('click', closeFullQuestionModal);
  
  window.addEventListener('click', (event) => {
      const modal = document.getElementById('fullQuestionModal');
      if (event.target === modal) {
          closeFullQuestionModal();
      }
  });
  
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

function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(`${tabId}-tab`).classList.add('active');
            
            updateUrlForTab(tabId);
        });
    });
    
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'pending';
    
    const currentActiveTab = document.querySelector('.tab-button.active');
    if (!currentActiveTab || currentActiveTab.getAttribute('data-tab') !== activeTab) {
        document.querySelector(`.tab-button[data-tab="${activeTab}"]`).click();
    }
}

function updateUrlForTab(tab) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    
    if (tab === 'pending') {
        url.searchParams.delete('sort-reported');
        url.searchParams.delete('page-reported');
        url.searchParams.delete('filter-type');
    } else {
        url.searchParams.delete('sort-pending');
        url.searchParams.delete('page-pending');
    }
    
    window.history.pushState({}, '', url);
}

function updatePendingSort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort-pending', sortValue);
    url.searchParams.set('tab', 'pending');
    window.location.href = url.toString();
}

function updateReportedSort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort-reported', sortValue);
    url.searchParams.set('tab', 'reported');
    window.location.href = url.toString();
}

function updateReportedFilter(filterValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('filter-type', filterValue);
    url.searchParams.set('tab', 'reported');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    setupTabs();
    
});
