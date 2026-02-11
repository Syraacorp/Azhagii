$(document).ready(function () {
  // ===========================
  // Mobile Navigation Toggle
  // ===========================
  $(".hamburger").click(function (e) {
    e.stopPropagation();
    $(".nav-links").toggleClass("active");
  });

  // Close mobile nav when clicking outside
  $(document).click(function (e) {
    if (
      !$(e.target).closest(".nav-links").length &&
      !$(e.target).closest(".hamburger").length
    ) {
      $(".nav-links").removeClass("active");
    }
  });

  // Close mobile nav when clicking a link
  $(".nav-links a").click(function () {
    if ($(window).width() <= 768) {
      $(".nav-links").removeClass("active");
    }
  });

  // Handle window resize to reset nav display
  $(window).resize(function () {
    if ($(window).width() > 768) {
      $(".nav-links").removeClass("active").removeAttr("style");
    }
  });

  // ===========================
  // Dashboard Sidebar Toggle
  // ===========================
  $("#menu-toggle").click(function () {
    $("#sidebar").toggleClass("active");
    $(".sidebar-overlay").toggleClass("active");
  });

  // Close sidebar when clicking overlay
  $(".sidebar-overlay").click(function () {
    $("#sidebar").removeClass("active");
    $(".sidebar-overlay").removeClass("active");
  });

  // ===========================
  // User Dropdown
  // ===========================
  $("#user-dropdown-trigger").click(function (e) {
    e.stopPropagation();
    var $dropdown = $("#user-dropdown");
    $dropdown.toggleClass("show");

    if ($dropdown.hasClass("show")) {
      $dropdown.css("display", "flex");
      setTimeout(function () {
        $dropdown.css({ opacity: "1", transform: "translateY(0)" });
      }, 10);
    } else {
      $dropdown.css({ opacity: "0", transform: "translateY(-10px)" });
      setTimeout(function () {
        $dropdown.css("display", "none");
      }, 200);
    }
  });

  // Close dropdown when clicking outside
  $(document).click(function (e) {
    if (!$(e.target).closest("#user-dropdown-trigger, #user-dropdown").length) {
      var $dropdown = $("#user-dropdown");
      $dropdown.removeClass("show");
      $dropdown.css({ opacity: "0", transform: "translateY(-10px)" });
      setTimeout(function () {
        $dropdown.css("display", "none");
      }, 200);
    }
  });

  // ===========================
  // Global SweetAlert confirmation
  // ===========================
  $(".confirm-action").click(function (e) {
    e.preventDefault();
    var url = $(this).attr("href");

    Swal.fire({
      title: "Are you sure?",
      text: "Do you want to proceed?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#4f46e5",
      cancelButtonColor: "#64748b",
      confirmButtonText: "Yes, proceed",
      cancelButtonText: "Cancel",
    }).then(function (result) {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });

  // ===========================
  // Auto-dismiss alerts after 5s
  // ===========================
  $(".alert").each(function () {
    var $alert = $(this);
    setTimeout(function () {
      $alert.fadeOut(300, function () {
        $(this).remove();
      });
    }, 5000);
  });
});
