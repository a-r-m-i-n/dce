const dceEditButton = document.querySelector('a[data-dce-edit-url]');

dceEditButton.addEventListener('click', function (event) {
    event.preventDefault();

    const editUrl = dceEditButton.dataset.dceEditUrl;
    window.open(editUrl, 'editDcePopup', 'height=768,width=1024,status=0,menubar=0,scrollbars=1')
});
