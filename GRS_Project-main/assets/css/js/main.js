// Main JavaScript file for Grievance Redressal System

document.addEventListener("DOMContentLoaded", () => {
    // Tab switching functionality for dashboard
    const tabItems = document.querySelectorAll(".tab-item")
    if (tabItems.length > 0) {
      tabItems.forEach((tab) => {
        tab.addEventListener("click", function () {
          const tabName = this.getAttribute("data-tab")
  
          // Update active tab
          document.querySelectorAll(".tab-item").forEach((t) => {
            t.classList.remove("active")
          })
          this.classList.add("active")
  
          // Filter grievances if on dashboard
          if (document.querySelector(".grievance-list")) {
            filterGrievances(tabName)
          }
  
          // Show/hide tab content if on detail page
          if (document.querySelector(".tab-content")) {
            document.querySelectorAll(".tab-content").forEach((content) => {
              content.style.display = "none"
            })
            document.getElementById(tabName).style.display = "block"
          }
        })
      })
    }
  
    // Password confirmation validation
    const passwordField = document.getElementById("password")
    const confirmPasswordField = document.getElementById("confirm_password")
  
    if (passwordField && confirmPasswordField) {
      confirmPasswordField.addEventListener("input", () => {
        if (passwordField.value !== confirmPasswordField.value) {
          confirmPasswordField.setCustomValidity("Passwords don't match")
        } else {
          confirmPasswordField.setCustomValidity("")
        }
      })
    }
  
    // Display flash messages
    displayFlashMessage()
  
    // Mobile sidebar toggle
    const menuToggle = document.querySelector(".menu-toggle")
    if (menuToggle) {
      menuToggle.addEventListener("click", () => {
        document.querySelector(".sidebar").classList.toggle("active")
      })
    }
  })
  
  // Function to filter grievances based on status
  function filterGrievances(status) {
    const grievanceItems = document.querySelectorAll(".grievance-item")
  
    if (status === "all") {
      grievanceItems.forEach((item) => {
        item.style.display = "flex"
      })
    } else {
      grievanceItems.forEach((item) => {
        const itemStatus = item.querySelector(".status-badge").classList.contains("status-" + status)
        item.style.display = itemStatus ? "flex" : "none"
      })
    }
  
    // Show empty state if no grievances match the filter
    const visibleItems = Array.from(grievanceItems).filter((item) => item.style.display !== "none")
    const emptyState = document.querySelector(".empty-state")
  
    if (visibleItems.length === 0 && emptyState) {
      emptyState.style.display = "block"
    } else if (emptyState) {
      emptyState.style.display = "none"
    }
  }
  
  // Function to display flash messages
  function displayFlashMessage() {
    // This would normally be populated from PHP session
    // For demo purposes, we'll check URL parameters
    const urlParams = new URLSearchParams(window.location.search)
    const message = urlParams.get("message")
    const messageType = urlParams.get("type") || "success"
  
    if (message) {
      // Create flash message element
      const flashMessage = document.createElement("div")
      flashMessage.className = `flash-message ${messageType}`
      flashMessage.innerHTML = `
              <div class="flash-content">
                  <span>${message}</span>
                  <button class="flash-close">&times;</button>
              </div>
          `
  
      // Add to document
      document.body.appendChild(flashMessage)
  
      // Add close button functionality
      const closeButton = flashMessage.querySelector(".flash-close")
      closeButton.addEventListener("click", () => {
        flashMessage.remove()
      })
  
      // Auto-remove after 5 seconds
      setTimeout(() => {
        flashMessage.remove()
      }, 5000)
    }
  }
  
  // Form validation functions
  function validateGrievanceForm() {
    const title = document.getElementById("title").value
    const category = document.getElementById("category").value
    const description = document.getElementById("description").value
  
    if (!title || !category || !description) {
      alert("Please fill in all required fields.")
      return false
    }
  
    return true
  }
  
  // Date formatting function
  function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }
  
  // Function to get URL parameters
  function getUrlParameter(name) {
    name = name.replace(/[[]/, "\\[").replace(/[\]]/, "\\]")
    const regex = new RegExp("[\\?&]" + name + "=([^&#]*)")
    const results = regex.exec(location.search)
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
  }
  