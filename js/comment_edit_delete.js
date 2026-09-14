document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-comment-btn").forEach(function (button) {
        button.addEventListener("click", async function () {
            const commentId = this.dataset.commentId;
            const textElement = document.getElementById("comment-text-" + commentId);
            if (!textElement) {
                return;
            }
            const currentComment = textElement.innerText.trim();
            const newComment = prompt("Edit your comment:", currentComment);
            if (newComment === null) {
                return;
            }
            const comment = newComment.trim();
            if (comment === "") {
                alert("Comment cannot be empty.");
                return;
            }
            if (comment.length > 2000) {
                alert("Comment cannot exceed 2000 characters.");
                return;
            }
            const formData = new FormData();
            formData.append("comment_id", commentId);
            formData.append("comment", comment);
            try {
                const response = await fetch("edit_comment.php", {
                    method: "POST",
                    body: formData
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || "Unable to edit comment.");
                    return;
                }
                textElement.textContent = data.comment;
            } catch (error) {
                console.error(error);
                alert("Something went wrong while editing the comment.");
            }
        });
    });
    document.querySelectorAll(".delete-comment-btn").forEach(function (button) {
        button.addEventListener("click", async function () {
            const commentId = this.dataset.commentId;
            const confirmed = confirm("Are you sure you want to delete this comment?");
            if (!confirmed) {
                return;
            }
            const formData = new FormData();
            formData.append("comment_id", commentId);
            try {
                const response = await fetch("delete_comment.php", {
                    method: "POST",
                    body: formData
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || "Unable to delete comment.");
                    return;
                }
                const commentElement = this.closest(".review-reply");
                if (commentElement) {
                    commentElement.remove();
                }
            } catch (error) {
                console.error(error);
                alert("Something went wrong while deleting the comment.");
            }
        });
    });
});