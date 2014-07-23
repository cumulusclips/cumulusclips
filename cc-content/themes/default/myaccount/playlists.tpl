<?php $this->SetLayout('myaccount'); ?>
<?php $playlistService = $this->getService('Playlist'); ?>

<h1><?=Language::GetText('playlists_header')?></h1>
        
<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>
    
    
<div class="playlist_list">
        
    <!-- Begin Favorites -->
    <div class="playlist">
        <?php if (count($favoritesList->entries) > 0): ?>
            <div class="thumbnails">
                <?php $thumbnails = getPlaylistThumbnails($favoritesList); ?>
                <img width="165" height="92" src="<?=$thumbnails[0]?>" />
                <?php if (count($favoritesList->entries) >= 2): ?>
                    <div>
                        <?php foreach (array_slice($thumbnails, 1) as $imgUrl): ?>
                            <img width="65" height="36" src="<?=$imgUrl?>" />
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <p><a href="<?=$playlistService->getUrl($favoritesList)?>"><?=Language::GetText('favorites')?></a></p>
        <?php else: ?>
            <img src="<?=THEME?>/images/playlist_placeholder.png" />
            <p><?=Language::GetText('favorites')?></p>
        <?php endif; ?>
        <p class="small"><strong><?=Language::GetText('videos')?>:</strong> <?=count($favoritesList->entries)?></p>
        <p class="actions small">
            <a href="<?=HOST?>/myaccount/playlists/edit/<?=$favoritesList->playlistId?>/" title="<?=Language::GetText('edit_playlist')?>"><?=Language::GetText('edit_playlist')?></a>
        </p>
    </div>
    
    <!-- Begin Watch Later -->
    <div class="playlist">
        <?php if (count($watchLaterList->entries) > 0): ?>
            <div class="thumbnails">
                <?php $thumbnails = getPlaylistThumbnails($watchLaterList); ?>
                <img width="165" height="92" src="<?=$thumbnails[0]?>" />
                <?php if (count($watchLaterList->entries) >= 2): ?>
                    <div>
                        <?php foreach (array_slice($thumbnails, 1) as $imgUrl): ?>
                            <img width="65" height="36" src="<?=$imgUrl?>" />
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <p><a href="<?=$playlistService->getUrl($watchLaterList)?>"><?=Language::GetText('watch_later')?></a></p>
        <?php else: ?>
            <img src="<?=THEME?>/images/playlist_placeholder.png" />
            <p><?=Language::GetText('watch_later')?></p>
        <?php endif; ?>
        <p class="small"><strong><?=Language::GetText('videos')?>:</strong> <?=count($watchLaterList->entries)?></p>
        <p class="actions small">
            <a href="<?=HOST?>/myaccount/playlists/edit/<?=$watchLaterList->playlistId?>/" title="<?=Language::GetText('edit_playlist')?>"><?=Language::GetText('edit_playlist')?></a>
        </p>
    </div>
    
</div>
 
<h2><?=Language::GetText('playlists')?></h2>
<p><a class="showhide" data-block="createNewPlaylist" href=""><?=Language::GetText('create_new_playlist')?></a></p>

<!-- BEGIN CREATE PLAYLIST FORM -->
<div id="createNewPlaylist">
    <h2><?=Language::GetText('create_new_playlist')?></h2>
    <div class="form playlists_form">
        <form method="POST">
            <div class="field">
                <label><?=Language::GetText('playlist_name')?>:</label>
                <input class="text" type="text" name="name" />
            </div>
            <div class="field">
                <label><?=Language::GetText('visibility')?>:</label>
                <select name="visibility">
                    <option value="public"><?=Language::GetText('public')?></option>
                    <option value="private"><?=Language::GetText('private')?></option>
                </select>
            </div>
            <input class="button" type="submit" value="<?=Language::GetText('create_playlist_button')?>" />
            <input type="hidden" name="submitted" value="true" />
        </form>
    </div>
</div>
<!-- END CREATE PLAYLIST FORM -->

<?php if (count($userPlaylists) > 0): ?>

    <div class="playlists_list">
    <?php foreach ($userPlaylists as $playlist): ?>
        <div class="playlist">
            <?php if (count($playlist->entries) > 0): ?>
                <div class="thumbnails">
                    <?php $thumbnails = getPlaylistThumbnails($playlist); ?>
                    <img width="165" height="92" src="<?=$thumbnails[0]?>" />
                    <?php if (count($playlist->entries) >= 2): ?>
                        <div>
                            <?php foreach (array_slice($thumbnails, 1) as $imgUrl): ?>
                                <img width="65" height="36" src="<?=$imgUrl?>" />
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <p><a href="<?=$playlistService->getUrl($playlist)?>"><?=$playlist->name?></a></p>
            <?php else: ?>
                <img src="<?=THEME?>/images/playlist_placeholder.png" />
                <p><?=$playlist->name?></p>
            <?php endif; ?>
            <p class="small"><strong><?=Language::GetText('videos')?>:</strong> <?=count($playlist->entries)?></p>
            <p class="actions small">
                <a href="<?=HOST?>/myaccount/playlists/edit/<?=$playlist->playlistId?>/" title="<?=Language::GetText('edit_playlist')?>"><?=Language::GetText('edit_playlist')?></a>
                <a class="right confirm" data-node="confirm_delete_playlist" href="<?=HOST?>/myaccount/playlists/?remove=<?=$playlist->playlistId?>" title="<?=Language::GetText('delete_playlist')?>"><?=Language::GetText('delete_playlist')?></a>
            </p>
        </div>
    <?php endforeach; ?>
    </div>

<?php else: ?>
    <p><strong><?=Language::GetText('no_playlists')?></strong></p>
<?php endif; ?>