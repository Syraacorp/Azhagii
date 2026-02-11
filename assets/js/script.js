$(document).ready(function () {
  // Mobile Navigation Toggle
  $(".hamburger").click(function () {
    $(".nav-links").slideToggle();
  });

  // Handle window resize to reset nav display
  $(window).resize(function () {
    if ($(window).width() > 768) {
      $(".nav-links").css("display", "flex");
    } else {
      $(".nav-links").css("display", "none");
    }
  });

  // Global SweetAlert confirmation for links with class 'confirm-action'
  $(".confirm-action").click(function (e) {
    e.preventDefault();
    const url = $(this).attr("href");

    Swal.fire({
      title: "Are you sure?",
      text: "Do you want to proceed?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
});
