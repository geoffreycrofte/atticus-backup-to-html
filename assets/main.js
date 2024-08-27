;(function(){

    const toc = document.querySelector('.toc');
    const dest = document.querySelector('header[role="banner"]');
    const div = document.createElement('div');

    div.setAttribute('class', 'toc-container');
    div.append(toc.cloneNode(true) );
    dest.append( div );

})();