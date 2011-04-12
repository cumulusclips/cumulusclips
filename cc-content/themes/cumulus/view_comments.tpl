        <div class="block-header" id="all-comments-header"><h1>View All Comments</h1></div>
        <?php

        if ($Success) {
            echo '<div id="success">' . $Success . '</div>';
        } elseif ($Errors) {
            echo '<div id="errors-found">' . $Errors . '</div>';
        }
        
        ?>

        <h3 class="post-header">Viewing Comments For: <a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->dashed?>/" title="<?=$video->title?>"><?=$video->title?></a></h3>';




        <?php if ($db->Count($result) > 0): ?>


            <!-- BEGIN Video comments loop -->
            <?php while ($row = $db->FetchRow ($result)): ?>

                <?php
                $comment = new VideoComment ($row[0], $db);
                $comment_user = new User ($comment->user_id, $db);
                $picture = "http://www.gravatar.com/avatar/" . md5 (strtolower ($comment_user->email)) . "?default=" . urlencode (HOST . '/images/user_placeholder.gif') . "&size=55";
                ?>

                <div class="block comment">
                    <div class="video-comment">
                        <p class="thumb"><a href="<?=HOST?>/channels/<?=$comment_user->username?>/" title="<?=$comment_user->username?>"><img src="<?=$picture?>" alt="<?=$comment_user->username?>" height="55" width="55" /></a></p>
                        <p>Posted by: <a href="<?=HOST?>/channels/<?=$comment_user->username?>/" title="<?=$comment_user->username?>"><?=$comment_user->username?></a></p>
                        <p>Date Posted: <?=$comment->comment_date?></p>
                        <p><a href="<?=HOST?>'/comments/videos/<?=$id?>/flag/<?=$comment->comment_id?>/" title="Report Abuse">Report Abuse</a></p>
                        <br clear="all" />
                    </div>
                    <?=$comment->comment?>
                </div>

            <?php endwhile; ?>
            <!-- END videos comments loop -->


        <?php else: ?>
            <div class="block"><strong>No comments have been posted for this video yet!</strong></div>
        <?php endif; ?>
        
        <?=$pagination->Paginate()?>