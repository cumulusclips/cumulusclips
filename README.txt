[PERMISSIONS]

Make sure the following directories are writeable by PHP and the webserver:

/cc-core/log
/cc-content/uploads/temp
/cc-content/uploads/flv
/cc-content/uploads/mp4
/cc-content/uploads/thumbs


[META & TITLE TAGS]

http://www.seomoz.org/learn-seo/meta-description



[PLUGIN LOCATIONS]

app.start - /cc-core/config/bootstrap.php

view.init - /cc-core/lib/View.php
view.load_page - /cc-core/lib/View.php
view.render - /cc-core/lib/View.php
view.set_layout - /cc-core/lib/View.php
view.header - /cc-core/lib/View.php
view.footer - /cc-core/lib/View.php
view.body - /cc-core/lib/View.php
view.block - /cc-core/lib/View.php
view.repeating_block - /cc-core/lib/View.php
view.repeating_block_loop - /cc-core/lib/View.php
view.add_sidebar_block - /cc-core/lib/View.php
view.output_sidebar_blocks - /cc-core/lib/View.php
view.output_sidebar_blocks_loop - /cc-core/lib/View.php
view.add_css - /cc-core/lib/View.php
view.write_css - /cc-core/lib/View.php
view.write_css_loop - /cc-core/lib/View.php
view.add_js - /cc-core/lib/View.php
view.write_js - /cc-core/lib/View.php
view.write_js_loop - /cc-core/lib/View.php
view.add_meta - /cc-core/lib/View.php
view.write_meta - /cc-core/lib/View.php
view.write_meta_loop - /cc-core/lib/View.php

index.start - /cc-core/controllers/index.php
index.pre_render - /cc-core/controllers/index.php

videos.start - /cc-core/controllers/videos.php
videos.pre_render - /cc-core/controllers/videos.php

