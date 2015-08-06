This folder holds images that are uploaded for items

NOTE: on deploy make sure the item_image folder is writeable by the apache user!

NOTE: on deploy you might need to run this selinux command to get this stuff working:
      chcon -t httpd_sys_rw_content_t /web/eqreserve/item_image
where /web/eqreserve/item_image should be changed to whatever your path to the item image folder is
