<?php
pdo_query("DROP TABLE IF EXISTS ".tablename('wwx_sign').";");
pdo_query("DROP TABLE IF EXISTS".tablename('wwx_sign_user').";");
pdo_query("DROP TABLE IF EXISTS".tablename('wwx_sign_record').";");
pdo_query("DROP TABLE IF EXISTS".tablename('wwx_sign_serial').";");
pdo_query("DROP TABLE IF EXISTS".tablename('wwx_sign_award').";");
pdo_query("DROP TABLE IF EXISTS".tablename('wwx_sign_link').";");