/**
 * Post Content Sync - Admin JavaScript
 */
(function ($) {
  'use strict';

  var ContentSync = {
    /**
     * Initialize
     */
    init: function () {
      this.bindEvents();
    },

    /**
     * Bind events
     */
    bindEvents: function () {
      $('#sync-all-files').on('click', this.handleSyncAll.bind(this));
      $('.sync-single-file').on('click', this.handleSyncSingle.bind(this));
    },

    /**
     * Handle sync all files
     */
    handleSyncAll: function (e) {
      e.preventDefault();

      var dryRun = $('#sync-dry-run').is(':checked');

      // Confirmation
      if (!dryRun && !confirm(postContentSync.strings.confirm)) {
        return;
      }

      // Show progress
      this.showProgress();
      $('#sync-result').hide();

      // Disable button
      $('#sync-all-files').prop('disabled', true);

      // AJAX request
      $.ajax({
        url: postContentSync.ajax_url,
        type: 'POST',
        data: {
          action: 'post_content_sync_all',
          nonce: postContentSync.nonce,
          dry_run: dryRun,
        },
        success: this.handleSyncAllSuccess.bind(this),
        error: this.handleSyncAllError.bind(this),
        complete: function () {
          $('#sync-all-files').prop('disabled', false);
        },
      });
    },

    /**
     * Handle sync all success
     */
    handleSyncAllSuccess: function (response) {
      this.hideProgress();

      if (response.success) {
        var data = response.data;
        var stats = data.stats;
        var results = data.results;
        var isDryRun = data.dry_run;

        // Build result HTML
        var html = '<div class="sync-resultーpanel notice notice-success"><p><strong>';
        if (isDryRun) {
          html += 'Dry Run Complete';
        } else {
          html += postContentSync.strings.success;
        }
        html += '</strong></p>';

        // Stats
        html += '<ul>';
        html += '<li>Total: ' + stats.total + '</li>';
        html += '<li>Created: ' + stats.created + '</li>';
        html += '<li>Updated: ' + stats.updated + '</li>';
        html += '<li>Skipped: ' + stats.skipped + '</li>';
        html += '<li>Errors: ' + stats.errors + '</li>';
        html += '</ul>';

        // Results table
        if (results.length > 0) {
          html += '<table class="widefat result-table">';
          html += '<thead><tr><th>File</th><th>Action</th><th>Message</th></tr></thead>';
          html += '<tbody>';

          results.forEach(function (result) {
            var actionLabel = result.action.replace('_', ' ').toUpperCase();
            html += '<tr>';
            html += '<td>' + result.file + '</td>';
            html += '<td>' + actionLabel + '</td>';
            html += '<td>' + result.message + '</td>';
            html += '</tr>';
          });

          html += '</tbody></table>';
        }

        html += '</div>';

        $('#sync-result').html(html).show();
      } else {
        this.handleSyncAllError(response);
      }
    },

    /**
     * Handle sync all error
     */
    handleSyncAllError: function (response) {
      this.hideProgress();

      var message = postContentSync.strings.error;
      if (response.data && response.data.message) {
        message = response.data.message;
      }

      var html =
        '<div class="notice notice-error"><p><strong>Error:</strong> ' + message + '</p></div>';
      $('#sync-result').html(html).show();
    },

    /**
     * Handle sync single file
     */
    handleSyncSingle: function (e) {
      e.preventDefault();

      var $button = $(e.currentTarget);
      var file = $button.data('file');

      // Disable button
      $button.prop('disabled', true).text('Syncing...');

      // AJAX request
      $.ajax({
        url: postContentSync.ajax_url,
        type: 'POST',
        data: {
          action: 'post_content_sync_single',
          nonce: postContentSync.nonce,
          file: file,
        },
        success: function (response) {
          if (response.success) {
            $button.text('Synced ✓');
            setTimeout(function () {
              location.reload();
            }, 1000);
          } else {
            $button.text('Error ✗').prop('disabled', false);
            alert('Error: ' + (response.data.message || 'Unknown error'));
          }
        },
        error: function () {
          $button.text('Error ✗').prop('disabled', false);
          alert('Error: Failed to sync file');
        },
      });
    },

    /**
     * Show progress
     */
    showProgress: function () {
      $('#sync-progress').show();
      $('.progress-bar-fill').css('width', '0%');
      $('.sync-status').text('Initializing...');

      // Animate progress (fake progress for now)
      var progress = 0;
      var interval = setInterval(function () {
        progress += 10;
        if (progress > 90) {
          progress = 90;
          clearInterval(interval);
        }
        $('.progress-bar-fill').css('width', progress + '%');
      }, 200);
    },

    /**
     * Hide progress
     */
    hideProgress: function () {
      $('.progress-bar-fill').css('width', '100%');
      setTimeout(function () {
        $('#sync-progress').fadeOut();
      }, 500);
    },
  };

  // Initialize on document ready
  $(document).ready(function () {
    ContentSync.init();
  });
})(jQuery);
