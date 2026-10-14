            </div> <!-- /content-container -->
        </main> <!-- /main-body -->

        <!-- Application Footer -->
        <footer class="app-footer">
            <div class="footer-container">
                <p>&copy; <?php echo date('Y'); ?> Student Management System &bull; Web Technology Assignment (CRUD &amp; Secure File Uploads)</p>
                <p class="footer-subtext">Built with PHP 8, MySQL (PDO), HTML5, CSS3 &bull; XAMPP Localhost Ready</p>
            </div>
        </footer>
    </div> <!-- /app-layout -->

    <!-- Reusable Deletion Confirmation Modal -->
    <div class="modal-backdrop" id="deleteModal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-warning-icon">⚠️</div>
                <h3 class="modal-title" id="modalTitle">Confirm Deletion</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteStudentName">this student</strong>?</p>
                <p class="modal-detail-text">This will permanently remove their academic records and <strong>all associated uploaded files</strong> from the server. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" action="delete_student.php" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                    <input type="hidden" name="id" id="deleteStudentId" value="">
                    <button type="submit" class="btn btn-danger">Yes, Delete Student</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Interactive Client Scripts -->
    <script>
        // Modal Handlers
        function confirmDelete(id, name, roll) {
            document.getElementById('deleteStudentId').value = id;
            document.getElementById('deleteStudentName').textContent = name + ' (' + roll + ')';
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Close modal when clicking backdrop
        window.addEventListener('click', function(e) {
            var modal = document.getElementById('deleteModal');
            if (e.target === modal) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
