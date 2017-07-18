/**
 * template to build x columns in a row.
 */
var buildXColsARow = function(strap, $result, docs, currentQuery,
                     total, currentPage, totalPages, pagination,
                     cols, panelBuilder) {

    var resultSummary = '';
    if(total > 0) {
        var end = currentQuery.start +
                  currentQuery.perPage - 1;
        end = end > total ? total : end;
        resultSummary =
            'Page <strong>' + currentPage + '</strong>' +
            ' Showing [<strong>' + currentQuery.start +
            '</strong> - <strong>' + end +
            '</strong>] of <strong>' +
            total + '</strong> total results';
    } else {
        // no result found
        resultSummary =
            '<strong>No results containing ' +
            'all your search terms were found.</strong>';
    }
    $('#search-info').html(resultSummary);

    // build a 6 columns to show
    $result.html("");

    //result.append('<div>' + pagination + '</div>');
    var colQueue =[];
    for(i = 0; i < docs.length; i++) {
        var oneDoc = docs[i];
        //var panel = acronymPanelStripper(acronym);
        //var panel = userProfilePanel(acronym);
        var panel = panelBuilder(oneDoc);
        colQueue.push(panel);
        // i count from 0
        // 6 acronyms for a row
        var ready2Row = (i + 1) % cols;
        if(ready2Row == 0) {
            $result.append('<div class="row">' +
                colQueue.join("") + '</div>');
            // reset the queue.
            colQueue = [];
        }
    }

    // check if we missed anything...
    if(colQueue.length > 0) {

        // append to the last row.
        $result.append('<div class="row">' +
            colQueue.join(" ") +
            '</div>');
    }

    // add the pagination at the bottom too.
    $result.append('<div>' + pagination + '</div>');

    return $result;
};

/**
 * build the Acronyms list, which will have 6 columns
 */
var buildAcronymsList = function(strap, $result, docs, currentQuery,
                     total, currentPage, totalPages, pagination) {

    // the function buildXColsARow is defined in file
    // layout-x-cols-a-row.js
    return buildXColsARow(strap, $result, docs, currentQuery, total,
                          currentPage, totalPages, pagination, 4,
                          acronymPanelStripper);
};

/**
 * builder function to strip out all wiki syntax.
 */
var acronymPanelStripper = function(acronym) {

    // try to remove some wiki markups.
    var desc = acronym['description'];
    // replace wiki syntax.
    var text = desc
       .replace(/.*may refer to:/g, '')
       .replace(/\[http.*/g, '')
       .replace(/\[\[Category:.*/g, '')
       .replace(/[\]\[\']/g, '')
       .replace(/\*/, '<li class="list-group-item">')
       .replace(/\*/g, '</li><li class="list-group-item">');

    var panel = '<div class="col-sm-3">' +
        '<h2 class="text-center">' +
        '<a href="' + acronym['url'] + '">' +
        acronym['title'] + '</a></h2>' +
        '<p><ul class="list-group">' + text + '</li></ul></p>' +
        '</div>';
    return panel;
};

/**
 * build the Acronyms list, which will have 6 columns
 */
var buildProfilesList = function(strap, $result, docs, currentQuery,
                     total, currentPage, totalPages, pagination) {

    // the function buildXColsARow is defined in file
    // layout-x-cols-a-row.js
    return buildXColsARow(strap, $result, docs, currentQuery, total,
                          currentPage, totalPages, pagination, 4,
                          userProfilePanel);
};

/**
 * builder function to build the user profile by using the
 * Bootstrap thumbnail.
 */
var userProfilePanel = function(profile) {

   console.log(profile);

    // try to remove some wiki markups.
    var desc = profile['content'];

    // thumbnail panel
    var panel =
'<div class="thumbnail" style="border: 0px">' +
'  <a href="' + profile['url'] + '">' +
'  <img src="' + profile['image'] +
            '" class="img-circle" width=100% alt="' +
            profile['title'] + '"/>' +
'  </a>' +
'  <div class="caption">' +
'    <h3><a href="' + profile['url'] + '">' + profile['title'] +
'</a></h3>' +
'    <p>' + desc +'</p>' +
'  </div>' +
'</div>';

    // using the pull-left try to make the text wrap around the
    // image.
    panel =
'<div>' +
'  <a class="pull-left" href="' + profile['url'] + '">' +
'  <img src="' + profile['image'] +
            '" class="img-circle" width=86 alt="' +
            profile['title'] + '"/>' +
'  </a>' +
'  <div>' +
'    <h4><a href="' + profile['url'] + '">' + profile['title'] +
'</a></h4>' +
'    <p>' + desc +'</p>' +
'  </div>' +
'</div>';

    // add the column div.
    panel = '<div class="col-sm-3">' + panel + '</div>'

    return panel;
};

/**
 * build featured articles list, which will have 2 columns
 */
var buildFeaturedArticles = function(strap, $result, docs, currentQuery,
                     total, currentPage, totalPages, pagination) {

    // the function buildXColsARow is defined in file
    // layout-x-cols-a-row.js
    // set for 2 columns a row.
    return buildXColsARow(strap, $result, docs, currentQuery, total,
                          currentPage, totalPages, pagination, 2,
                          articlePanel);
};

/**
 * builder function to build the user profile by using the
 * Bootstrap thumbnail.
 */
var articlePanel = function(profile) {

    //console.log(profile);

    // try to remove some wiki markups.
    var desc = profile['description'];

    // get ready the img tag.
    var imgTag = '';
    if(profile['image']) {
        // using the pull-left try to make the text wrap around the
        // image.
        imgTag =
'  <a class="pull-left" href="' + profile['url'] + '">' +
'  <img src="' + profile['image'] +
//            '" class="img-circle" width=150 alt="' +
            '" class="img-rounded" width=150 alt="' +
            profile['title'] + '"/>' +
'  </a>';
    }

    // get ready the modified date.
    var modifiedDate = moment(profile['lastModifiedDate']);

    var panel =
'<div>' +
imgTag +
'  <div>' +
'    <h4><a href="' + profile['url'] + '">' + profile['title'] +
'</a></h4>' +
'    <p>' +
//'    <span class="label label-default">' +
'    <span class="text-success"><strong>' +
modifiedDate.format("MMMM DD, YYYY") + '</strong></span> - ' +
     desc +'</p>' +
'  </div>' +
'</div>';

    // add the column div.
    panel = '<div class="col-sm-6">' + panel + '</div>'

    return panel;
};
