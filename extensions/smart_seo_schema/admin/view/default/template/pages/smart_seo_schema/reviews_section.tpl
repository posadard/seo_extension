<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-star"></i> Product Reviews Management
            <small class="text-muted">- Real reviews from database with AI optimization</small>
        </h4>
    </div>
    <div class="panel-body">
        
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Manage actual product reviews</strong> from your database. 
            Use AI to optimize existing reviews or generate example reviews for better Schema.org structured data.
        </div>

        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-6">
                <h5><i class="fa fa-list"></i> Current Reviews (<?php echo count($product_reviews); ?>)</h5>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" onclick="editReview('new')">
                        <i class="fa fa-plus"></i> Add New Review
                    </button>
                    <button type="button" id="generate_example_review" class="btn btn-primary btn-sm" onclick="generateExampleReview()">
                        <i class="fa fa-star"></i> Generate Example Review
                    </button>
                </div>
            </div>
        </div>

        <div class="reviews-container">
            <?php if (empty($product_reviews)): ?>
                <div class="alert alert-warning text-center">
                    <i class="fa fa-exclamation-triangle fa-2x"></i><br>
                    <strong>No reviews found for this product</strong><br>
                    <small>Click "Add New Review" to create one manually or "Generate Example Review" to create one with AI</small>
                </div>
            <?php else: ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="15%">Author</th>
                                <th width="35%">Review Text</th>
                                <th width="10%">Rating</th>
                                <th width="10%">Status</th>
                                <th width="12%">Date</th>
                                <th width="18%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($product_reviews as $review): ?>
                            <tr id="review_row_<?php echo $review['review_id']; ?>" class="review-row">
                                <td>
                                    <strong id="review_author_<?php echo $review['review_id']; ?>"><?php echo htmlspecialchars($review['author']); ?></strong>
                                    <?php if ($review['verified_purchase']): ?>
                                        <br><small class="text-success"><i class="fa fa-check-circle"></i> Verified</small>
                                    <?php endif; ?>
                                    
                                    <input type="hidden" id="review_verified_<?php echo $review['review_id']; ?>" 
                                           data-verified="<?php echo $review['verified_purchase']; ?>">
                                    <input type="hidden" id="review_status_<?php echo $review['review_id']; ?>" 
                                           data-status="<?php echo $review['status']; ?>">
                                </td>
                                <td>
                                    <textarea id="review_text_<?php echo $review['review_id']; ?>" 
                                              class="form-control" 
                                              rows="3" 
                                              style="resize: vertical; font-size: 12px;"><?php echo htmlspecialchars($review['text']); ?></textarea>
                                    <small class="text-muted">
                                        <?php echo strlen($review['text']); ?> characters
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fa fa-star"></i>
                                            <?php else: ?>
                                                <i class="fa fa-star-o"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <small><?php echo $review['rating']; ?>/5</small>
                                    
                                    <input type="hidden" id="review_rating_<?php echo $review['review_id']; ?>" 
                                           data-rating="<?php echo $review['rating']; ?>">
                                </td>
                                <td class="text-center">
                                    <?php if ($review['status']): ?>
                                        <span class="label label-success">Active</span>
                                    <?php else: ?>
                                        <span class="label label-default">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($review['date_added'])); ?>
                                        <?php if ($review['date_modified'] != $review['date_added']): ?>
                                            <br><em>Modified: <?php echo date('M j', strtotime($review['date_modified'])); ?></em>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" style="width: 100%;">
                                        <button type="button" 
                                                id="optimize_btn_<?php echo $review['review_id']; ?>"
                                                class="btn btn-warning btn-optimize" 
                                                onclick="optimizeReview(<?php echo $review['review_id']; ?>, $('#review_text_<?php echo $review['review_id']; ?>').val())"
                                                title="Optimize this review with AI">
                                            <i class="fa fa-magic"></i> Optimize
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-info btn-sm" 
                                                onclick="editReview(<?php echo $review['review_id']; ?>)"
                                                title="Edit review details">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="deleteReview(<?php echo $review['review_id']; ?>)"
                                                title="Delete this review">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php endif; ?>
        </div>

        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-12">
                <div class="alert alert-success">
                    <strong><i class="fa fa-lightbulb-o"></i> AI Features:</strong>
                    <ul class="list-unstyled" style="margin-top: 10px; margin-bottom: 0;">
                        <li><i class="fa fa-magic text-warning"></i> <strong>Optimize:</strong> Improve existing review content while maintaining authenticity</li>
                        <li><i class="fa fa-star text-primary"></i> <strong>Generate Example:</strong> Create sample reviews (3-5 stars) based on product description</li>
                        <li><i class="fa fa-database text-info"></i> <strong>Real Data:</strong> All reviews are stored in your database and used for Schema.org markup</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>