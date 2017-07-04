# PHP: One
A PHP Framework: One

### structure & checklist
- foo/
  - bar/
    - log/ : _**Check the writable permission for application executor**_
    - src/ : _**Web Root**_
      - Config/ (Modify to suit your needs)
        - accounts.php : account for DB, Cache
        - defines.php : FDEF_DATA_..., FDEF_PACKET_...

### src/Config/defines.php
if _`FDEF_DATA_WRITEONCE`_ is _`true`_ it will be write all manipulated data at once to _[DB [, Cache]]_ at end of application life cycle
