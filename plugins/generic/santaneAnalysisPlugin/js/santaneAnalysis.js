/**
 * @file santaneAnalysis.js
 * 
 * JavaScript for Santane Analysis plugin functionality
 */
(function($) {
    $(document).ready(function() {
        // Only run if we're on the submission page
        if ($('textarea[name="abstract"]').length) {
            // Inject the analysis button and results container
            $('textarea[name="abstract"]').after(
                '<div class="santane_analysis_section">' +
                '<button type="button" id="santaneAnalysisButton" class="pkp_button">Run Santane AI Analysis</button>' +
                '<div id="santaneAnalysisResults" class="santane_analysis_results"></div>' +
                '</div>'
            );
            
            // Attach event listener to the button
            $('#santaneAnalysisButton').click(function() {
                runSantaneAnalysis();
            });
        }
    });
    
    /**
     * Run the Santane AI analysis
     */
    function runSantaneAnalysis() {
        const abstractText = $('textarea[name="abstract"]').val();
        const resultsContainer = $('#santaneAnalysisResults');
        const button = $('#santaneAnalysisButton');
        
        // Show loading state
        button.prop('disabled', true).text('Analyzing...');
        resultsContainer.html('<div class="pkp_helpers_progress">Loading...</div>');
        
        // Make AJAX request to our custom API endpoint
        $.ajax({
            url: 'index.php/index/santane/analyze',
            type: 'POST',
            data: {
                abstract: abstractText
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    resultsContainer.html('<div class="pkp_form_error">Error: ' + response.error + '</div>');
                } else {
                    // Display analysis results
                    let html = '<div class="santane_analysis_report">';
                    html += '<h4>Santane AI Analysis Results</h4>';
                    html += '<ul>';
                    html += '<li><strong>Word Count:</strong> ' + response.wordCount + '</li>';
                    html += '<li><strong>Sentence Count:</strong> ' + response.sentenceCount + '</li>';
                    html += '<li><strong>Clarity Score:</strong> ' + response.clarityScore + '/100</li>';
                    
                    if (response.foundKeywords && response.foundKeywords.length > 0) {
                        html += '<li><strong>Keywords Found:</strong> ' + response.foundKeywords.join(', ') + '</li>';
                    }
                    
                    html += '</ul>';
                    
                    if (response.recommendations && response.recommendations.length > 0) {
                        html += '<h5>Recommendations:</h5><ul>';
                        response.recommendations.forEach(function(rec) {
                            html += '<li>' + rec + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    html += '</div>';
                    
                    resultsContainer.html(html);
                }
            },
            error: function(xhr, status, error) {
                resultsContainer.html('<div class="pkp_form_error">Error: Unable to process analysis. Please try again later.</div>');
                console.error('Santane Analysis Error:', error);
            },
            complete: function() {
                button.prop('disabled', false).text('Run Santane AI Analysis');
            }
        });
    }
}(jQuery));