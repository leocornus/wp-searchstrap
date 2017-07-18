/**
 * jQuery plugin to present a simple search function.
 *
 *   jQuery('#div-id').searchStrap();
 */

;(function($) {

    // plugin name and default values.
    var pluginSearchStrap = "searchStrap";

    // set the default values / options.
    var defaults = {
        // the url endpoint for search.
        searchUrl : '/search',
        // the placeholder for search input box.
        placeholder: 'Find ...',
        // the kkkkkk
        itemsPerPage: 10,
        // query param for search term.
        queryName : 'searchterm',

        /**
         * set the default sort.
         */
        sort: 'lastModifiedDate desc',

        // options for facet, based on Solr
        // here are default facet.
        facet: {
            //TODO: facetQuery: ['term one', 'term two'],
            // only support facet field for now.
            facetField: ['site', 'authors', 'keywords']
        },

        /**
         * the filter query, using Solr query syntax.
         * for example: "site: wiki AND keywords: Acronyms"
         * default is empty, which will search all content.
         */
        fq: '',

        /**
         * the field list, a list of fields you want to get 
         * from the search engine.
         */
        fl: 'id,title,site,url,description,conent,keywords,lastModifiedDate',

        // search input builder.
        searchBoxBuilder: null, // it will include the summary
        summaryBuilder: null,

        // build the pagenation nav bar.
        paginationBuilder: null,

        // jQuery selector for the the search result list section.
        resultSelector: '#search-result',
        // set the template for search result.
        // available templates: 2Columns, AcronymsList
        // empty will means using the default.
        resultTemplate: null,
        // set the function to build the HTML for each item.
        // we could NOT use prototype function here. 
        // we could set to use default prototype function by set this 
        // options to empty (null), 
        // then we could use the default builder function.
        // result filter panel, it could be empty.
        filterSelector: '#search-filter',
        // the template to build filter panel.
        filterTemplate: null,
        //panelFunction: function(item) {
        //    // define the default function for each item.
        //    var panel = '<h2><a href="' + item['url'] + '">' +
        //        item['title'] + '</a></h2>';
        //    return panel;
        //},
        // builders: paginagion, summary, result list, 
        // filters, facets

        // refresh / reload the search result list automatically.
        // this is basically hook the keyup event.
        // default is false,
        autoReload: false
    };

    // the plugin constructor.
    function Plugin(element, options) {

        // the DOM element.
        this.element = element;
        this.$element = $(element);
        // merge the facet options.
        //var facet = $.extend({}, defaults.facet, options.facet);
        //options.facet = facet;
        // extend mthod will merge object contents.
        // the first param is set recursive merge or not!
        this.settings = $.extend(false, {}, defaults, options);
        this._defatuls = defaults;
        this._name = pluginSearchStrap;
        this.init();
    }

    // add the plugin to the jQuery chain.
    $.fn[pluginSearchStrap] = function(options) {

        // return to maintain the chain.
        return this.each(function() {
            // check the local storage index for the current
            // element.
            if(!$.data(this, "plugin_" + pluginSearchStrap)) {
                // no plugin created yet, let create a new one.
                $.data(this, "plugin_" + pluginSearchStrap, 
                       new Plugin(this, options));
            }
        });
    };

    // use extend method to avoid Plugin prototype confilcts.
    $.extend(Plugin.prototype, {

        // the initialize function.
        init: function() {

            var self = this;

            // build the search box.
            // TODO: make this configurable!
            // reference Gooble search, search box will inlcue
            // - search input box. $searchInput
            // - search button. $searchButton
            // - search result summary $searchSummary
            // - top category tabs. TODO
            // - you could add your own here!
            self.buildSearchBox();

            // we will get search term from query
            // TODO: more are comming: facets, sort, etc.
            var paramName = self.settings.queryName;
            var queryParams = self.getUrlVars();

            // handle search term.
            // set to empty string if no query parameter found.
            var searchTerm = paramName in queryParams ?
                             queryParams[paramName] : '';
            searchTerm = decodeURIComponent(searchTerm);
            // set the initial value for input box
            self.$searchInput.val(searchTerm);
            // show the glyphicon remove.
            self.toggleRemoveIcon();
            // trigger the propertychange event. 
            // some function depends on this event.
            // e.g., the clear button using Bootstrap feedback icon
            // will depen on this event.
            self.$searchInput.trigger('propertychange');

            // set the start to 1 if we could not find it.
            var start = 'start' in queryParams ?
                        queryParams['start'] : 1;
            // TODO: handle facets, sorting, etc.

            // prepare the query to perform the initial search
            var searchQuery =
                this.prepareSearchQuery(searchTerm, start);
            // the initial search.
            self.search(searchQuery);
        },

        /**
         * parse the URL got the query parameters.
         */
        getUrlVars : function() {

            var vars = [], hash;
            var href = window.location.href;
            var hashes = href.slice(href.indexOf('?') + 1).split('&');
            for(var i = 0; i < hashes.length; i++)
            {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }

            return vars;
        },

        /**
         * utility method to build the search query.
         * only handle search term and pagination now.
         * TODO: Will add field query soon.
         *
         * default start item is 1, as Solr counts from 1
         */
        prepareSearchQuery: function(term, start) {

            // set the default value to 1 for start.
            var start = typeof start !== 'undefined' ? start : 1;

            var searchQuery = {
                term: term,
                start: start
            };

            return searchQuery;
        },

        /**
         * Perform search according to the search query.
         * Search query will track the search states, including:
         *
         * - search term
         * - start number
         * - TODO facets, filter query, 
         */
        search: function(searchQuery) {

            var self = this;

            // get ready action data for search endpoint.
            var thefacet = {
                    facetField: ['site', 'keywords', 'authors']
            };
            var searchAction = {
                term: searchQuery.term,
                start: searchQuery.start,
                perPage: self.settings.itemsPerPage,
                sort: self.settings.sort,
                // facet
                facet: JSON.stringify(self.settings.facet),
                fq: self.settings.fq,
                fl: self.settings.fl
            };

            $.ajax({
                url: self.settings.searchUrl,
                method: 'GET',
                dataType: 'json',
                // data object will 
                data: searchAction,
                success: function(data) {
                    self.handleSearchResult(data);
                }
            });
        },

        /**
         * handle button click event or enter key press.
         * we will reset search for both cases.
         */
        handleButtonClick : function() {

            var term = this.$searchInput.val();
            // prepare the query to perform the initial search
            // this is a new search, reset start to 1
            var query = this.prepareSearchQuery(term, 1);
            // no need search again if we will reload the page.
            //this.search(query);

            // update the brwoser url to
            // reflect the search input field
            this.updateBrowserUrl(query, true);
        },

        /**
         * update the url on browser
         */
        updateBrowserUrl: function(searchQuery, reload) {

            var reload =
                typeof reload === 'undefined' ? false : true;
            // Check first, if the query term is empty,
            // we don't need the query parameter!
            // build the search term parameter.
            var query = [];
            if(searchQuery.term.length > 0) {
                query.push(this.settings.queryName +
                    '=' + encodeURIComponent(searchQuery.term));
            }
            // add the start parameter.
            query.push('start=' + searchQuery.start);
            var url = '?' + query.join('&');

            // the push state will keep the url in history,
            // so the back will remember it.
            if (reload) {
                window.location.href = url;
            } else {
                window.history.pushState('', 'testing', url);
            }
        },

        /**
         * handle search result. it will mainly 
         * - calculate numbers,
         * - build the document list, 
         *   including summary and pagination
         * - hook events.
         */
        handleSearchResult: function(data) {

            var self = this;

            // log the data for debuging...
            console.log(data);

            // save the current query, we need it for next page.
            var currentQuery = data.currentQuery;

            // TODO: analyze the search result.
            // =========================================
            // analyze the result and collect data!

            // total results.
            var total = data.total;
            // calculate current page.
            var currentPage =
                (currentQuery.start - 1) / currentQuery.perPage + 1;
            // calculate the total pages.
            var totalPages = Math.ceil(total / currentQuery.perPage);

            // ============================================
            // build components for data

            // build the pagination bar.
            // TODO: make the pagination builder.
            var pagination = totalPages <= 1 ? '' :
                self.buildPagination(currentPage, totalPages);

            // build the summary of search result.
            var searchSummary = 
                self.buildSearchSummary(currentQuery, total,
                                        currentPage, totalPages);
            self.$searchSummary.html(searchSummary);

            // build the result list panel.
            var $result = 
                self.buildResultListPanel(data.docs, currentQuery,
                    total, currentPage, totalPages, pagination);

            // hook click event for all available pages on 
            // pagination nav bar.
            // TODO: set up the parameter: activePagesSelector:
            $result.find('nav ul li[class!="active"] a').
                on('click', function(event) {

                // identify the page.
                var searchTerm = currentQuery.term;
                var perPage = currentQuery.perPage;
                self.handlePagination($(this), searchTerm, 
                                      currentPage, totalPages, 
                                      perPage);
            });

            // build the result filter panel.
            // TODO: get ready facets and current query.
            self.buildResultFilterPanel(data.facets, currentQuery);
        },

        /**
         * build the search box and hook up events.
         */
        buildSearchBox: function() {

            var self = this;

            if(this.settings.searchBoxBuilder) {
                // use the customize search box 
                self.settings.searchBoxBuilder(self);
            } else {
                // using the default search box.
                self.defaultSearchBox(self);
            }

            // hook the click event to search button.
            //console.log(self.settings.searchButton);
            self.$searchButton.on('click', function() {
                self.handleButtonClick();
            });

            // hook the key press event.
            self.$searchInput.on('keypress', function(event) {

                //console.log(event);
                // only handle the enter key.
                if(event.keyCode == 13) {
                    self.handleButtonClick();
                }
            });

            // hook the key up event for the input field.
            self.$searchInput.on('keyup', function(event) {

                self.toggleRemoveIcon();

                if(self.settings.autoReload) {
                    var term = $(this).val();
                    if (term.length >= 0) {
                        // prepare the query to perform 
                        // the initial search
                        var query = self.prepareSearchQuery(term, 1);
                        self.search(query);
                    }
                }
            });
        },

        /**
         * toggle removal icon, we assume glyphicon-remove is used 
         * as the removal icon.
         * TODO: make this configurable.
         */
        toggleRemoveIcon: function() {

            var self = this;

            if(self.$searchInput.val()) {
                // show the glyphicon remove.
                self.$element.find('.glyphicon-remove').
                     removeClass('hidden');
            } else {
                self.$element.find('.glyphicon-remove').
                     addClass('hidden');
            }
        },

        /**
         * build the search summary.
         */
        buildSearchSummary: function(currentQuery, total, 
                                     currentPage, totalPages) {

            // TODO: add the configurable logic.
            var summary = 
                this.defaultSearchSummary(currentQuery, total,
                                          currentPage, totalPages);

            return summary;
        },

        /**
         * using the current query and total to build the 
         * pagination bar.
         */
        buildPagination: function(currentPage, totalPages) {

            var self = this;

            // TODO: add configurable logic!
            var pagination = 
                self.defaultPaginationDots(self, 
                                           currentPage, totalPages);

            return pagination;
        },

        /**
         * handle the pagination.
         */
        handlePagination: function($href, term, currentPage,
                                   totalPages, perPage) {

            var pageText = $href.text();
            var nextPage = 1;
            //console.log('page = ' + pageText);
            if(pageText.includes('First')) {
                // the first page button. do nothing using 
                // the default, start from 1
                nextPage = 1;
            } else if(pageText.includes('Last')) {
                // last page.
                nextPage = totalPages;
            } else if(pageText.includes('Previous')) {
                // previous page.
                nextPage = currentPage - 1;
            } else if(pageText.includes('Next')) {
                // the next page.
                nextPage = currentPage + 1;
            } else {
                // get what user selected.
                nextPage = parseInt(pageText);
            }
            var start = (nextPage - 1) * parseInt(perPage) + 1;

            // calculate start number to build search query.
            var query = 
                this.prepareSearchQuery(term, start);
            this.search(query);
            this.updateBrowserUrl(query);
        },

        /**
         * build the result list panel
         */
        buildResultListPanel: function(docs, currentQuery, total,
                               currentPage, totalPages, pagination) {

            var self = this;

            // here the result list DOM object.
            var $result = $(self.settings.resultSelector);

            // build the result page based on the result template.
            if (self.settings.resultTemplate) {
                $result = self.settings.resultTemplate(self, $result,
                        docs, currentQuery, total, 
                        currentPage, totalPages, pagination);
            } else {
                // using the default template, in file
                // templates/2cols.js
                $result = self.defaultResultList(self, $result,
                        docs, currentQuery, total,
                        currentPage, totalPages, pagination);
            }

            return $result;
        },

        /**
         * build the result filter panel.
         */
        buildResultFilterPanel: function(facets, currentQuery) {

            var self = this;
            //console.log(facets);

            // here the result list DOM object.
            var $filter = $(self.settings.filterSelector);

            // build the result page based on the result template.
            if (self.settings.filterTemplate) {
                $filter = self.settings.filterTemplate(self, $filter,
                        facets, currentQuery);
            } else {
                // using the default template, in file
                $filter = self.defaultResultFilter(self, $filter,
                        facets, currentQuery);
            }

            return $filter;
        },

        /**
         * build simple search result list, including:
         *   1. basic summary for search rsult.
         *   2. straight list-group for the document list.
         *   3. pagination.
         */
        buildSimpleResult: function(docs, currentQuery, total, 
                                    currentPage, totalPages) {

            var self = this;

            // build the info bar.
            var info = this.buildInfoBar(currentQuery.term, total,
                                         currentPage, totalPages);

            // using list group for search result.
            var $ul = $('<ul class="list-group"></ul>');
            $.each(docs, function(index, item) {
                // present each item as a list group item.
                var liHtml = self.buildMediaItemHtml(item);
                $ul.append(liHtml);
            });

            // build the pagination bar.
            var pagination = 
                this.buildPagination(currentPage, totalPages);

            // the search result section: 
            var $result = $(self.settings.resultSelector);
            $result.html('').append(info)
                            .append($ul).append(pagination);

            return $result;
        },

        /**
         * build the information bar for search result.
         */
        buildInfoBar: function(term, total, currentPage, totalPages) {

            var info =  '<p class="text-info">' +
                'Found ' + total + ' results (0.51 seconds) ' +
                'for <span class="label-info">' + 
                term + '</span>' +
                '</p>';

            return info;
        },

        /**
         * build the HTML for each item
         *
         * @param item object of each item
         */
        buildItemHtml: function(item) {

            var liHtml =
                '<li class="list-group-item">' +
                '  <h4 class="list-group-item-heading">' +
                '    <a href="' + item.url +
                            '" style="padding: 0;">' +
                       item.title +
                '    </a>' +
                '  </h4>' +
                '  <small class="text-muted">SITE</small>' +
                '  <p class="list-group-item-text">' +
                     item.description +
                '  </p>' +
                '</li>';

            return liHtml;
        },

        /**
         * build the HTML for each item using media object.
         *
         * @param item object of each item
         */
        buildMediaItemHtml: function(item) {

            var itemHtml = 
              '<li class="list-group-item">' +
              '<div class="media">' +
              '  <div class="media-left">' + 
              '    <span class="text-warning fa-stack fa-lg">' +
              '      <i class="fa fa-circle fa-stack-2x"></i>' +
              '      <i class="fa fa-file-text-o fa-stack-1x fa-inverse"></i>' +
              '    </span>' +
              '  </div>' + 
              '  <div class="media-body">' +
              '    <h4 class="media-heading">' +
                     '<a href="' + item.url + 
                         '" style="padding: 0;">' + item.title + 
                     '</a>' +
              '    </h4>' +
              '    <small class="text-muted">' + 
                     item.site + '</small>' +
              '    <p class="media-description">' + 
                     item.description + 
              '    </p>' + 
              '  </div>' +
              '</div>' + 
              '</li>';

            return itemHtml;
        },

        /*******************************************
         * some of the default builders and templates.
         */

        /**
         * the default builder to build search input box
         * this will depend on Bootstrap
         */
        defaultSearchBox: function(strap) {

            // build the search box.
            var searchBox = 
'<div class="input-group input-group-lg"' +
'     role="group" aria-label="...">' +
'  <div class="form-group form-group-lg has-feedback has-clear">' +
'    <input type="text" class="form-control"' +
'           placeholder="' + strap.settings.placeholder + '"' +
'           id="search-input"' +
'           aria-describedby="sizing-addon"/>' +
'    <span class="form-control-clear text-danger' +
'                 glyphicon glyphicon-remove' +
'                 form-control-feedback hidden"></span>' +
'  </div>' +
'  <span class="input-group-addon" id="search-button"' +
'        style="cursor: pointer">' +
'    <span class="glyphicon glyphicon-search ' +
'                 text-primary"></span> Search' +
'  </span>' +
'</div>' +
// the info bar. summary of search result.
'<div class="text-muted h4" id="search-info">' +
'  <h2>Loading...</h2>' +
'</div>';

            strap.$element.html('').append(searchBox);
            // set up the searchInput jQuery object..
            strap.$searchInput = strap.$element.find('input');
            // set up the searchButton jQuery object
            strap.$searchButton = 
                strap.$element.find('#search-button');
            // set up the search summary.
            strap.$searchSummary = 
                strap.$element.find('#search-info');

            // hook the clik event on the remove icon.
            strap.$element.find('.glyphicon-remove')
                .on('click', function(event) {

                strap.$searchInput.val('');
                // hide the remove icon.
                $(this).addClass('hidden');
                // trigger search and reload page.
                strap.handleButtonClick();
            });
        },

        /**
         * build the default pagination 
         */
        defaultPagination: function(currentPage, totalPages) {

            // border check up (first, last)
            // previous button will be handled by first page.
            // next button will be hanelded by last page.
            var pagination = '<nav class="text-center">' +
                             '<ul class="pagination"' +
                             '    style="cursor: pointer">';

            // decide the previous page.
            if(currentPage !== 1) {
                pagination = pagination + 
                    this.buildAPage('First') +
                    this.buildAPage('&laquo; Previous');
            }

            // generate the page list.
            for(var page = 1; page <= totalPages; page ++) {

                // normal page.
                var thePage = this.buildAPage(page);
                if(page == currentPage) {
                    thePage = this.buildAPage(page, 'active');
                }
                pagination = pagination + thePage;
            }

            // decide the next page.
            if(currentPage !== totalPages) { 
                pagination = pagination +
                    this.buildAPage('Next &raquo;') +
                    this.buildAPage('Last');
            }

            // add the ending tags.
            pagination = pagination + '</ul></nav>';
            return pagination;
        },
        
        /**
         * build the default pagination with ... and 
         * without First and Last button.
         */
        defaultPaginationDots: function(strap, currentPage,
                totalPages, surroundingPages, tailingPages) {

            // set default value for surrouning and tailing pages.
            var surroundingPages = 
                typeof surroundingPages !== 'undefined' ?
                surroundingPages : 1;
            var tailingPages = 
                typeof tailingPages !== 'undefined' ?
                tailingPages : 2;

            var pagination = '<nav class="text-center">' +
                             '<ul class="pagination"' +
                             '    style="cursor: pointer">';

            var thePage = '';
            // decide the previous page button
            if(currentPage !== 1) {
                thePage = this.buildAPage('&laquo; Previous');
            } else {
                thePage = this.buildAPage('&laquo; Previous', 'disabled');
            }
            pagination = pagination + thePage;

            // calculate the start page:
            // - set startPage = currentPage - 2 
            // - if startPage < 1 then set startPage = 1
            var startPage = currentPage - surroundingPages;
            startPage = startPage < 1 ? 1 : startPage;

            // calculate the end page.
            // - assumet we get the start page.
            // - set endPage = startPage + 5 - 1
            // - if endPage > totalPages set endPage = totalPages
            var endPage = startPage + (surroundingPages * 2);
            endPage = endPage > totalPages ? totalPages : endPage

            // decide the start page again based on the end page.
            startPage = endPage - (surroundingPages * 2);

            // decide the first page and first ... page
            // - if startPage <= 3 then no ... page
            //   - we will have all pages before start page.
            //   - simplely set the startPage = 1
            startPage = startPage <= (tailingPages + 2) ? 
                        1 : startPage;
            // - else the case (startPage > 4)
            //   - we will have first page and first ... page.
            //   - build the first page and the first ... page.
            if(startPage > 1) {
                // build the first page and the first ... page
                // build the tailing pages at the beginning.
                for(var i = 1; i <= tailingPages; i++) {
                    thePage = this.buildAPage(i);
                    pagination = pagination + thePage;
                }
                // build the first ... page.
                thePage = this.buildAPage('...', 'disabled');
                pagination = pagination + thePage;
            }

            // decide the last page and last ... page
            // - if endPage >= totalPages - 2 then no need ... page.
            //   - we will build all pages to total pages.
            //   - simplely set endPage = totalPages.
            endPage = endPage >= (totalPages - tailingPages - 1) ? 
                      totalPages : endPage;

            // generate the page list from start to end pages..
            for(var page = startPage; page <= endPage; page ++) {

                // normal page.
                var thePage = this.buildAPage(page);
                if(page == currentPage) {
                    // active page.
                    thePage = this.buildAPage(page, 'active');
                }
                pagination = pagination + thePage;
            }

            // - else (endPage < totalPages - 2)
            //   - we have build the last ... page and last page.
            if(endPage < totalPages) {

                // build the first page and the last ... page
                thePage = this.buildAPage('...', 'disabled');
                pagination = pagination + thePage;
                // build the tailing page at the end.
                for(var i = tailingPages - 1; i >= 0; i--) {
                    thePage = this.buildAPage(totalPages - i);
                    pagination = pagination + thePage;
                }
            }

            // decide the next page button.
            if(currentPage !== totalPages) { 
                thePage = this.buildAPage('Next &raquo;');
            } else {
                thePage = this.buildAPage('Next &raquo;', 'disabled');
            }
            pagination = pagination + thePage;

            // add the ending tags.
            pagination = pagination + '</ul></nav>';
            return pagination;
        },

        /**
         * utility method to build a page button on the pagination.
         *
         * the number class could be active, disabled, or nothing
         */
        buildAPage: function(number, numberClass) {

            // set default number class to nothing.
            var numberClass = typeof numberClass !== 'undefined' ?
                              numberClass : '';
            var page = 
                '<li class="' + numberClass + '"><a><span>' +
                number + 
                '</span></a></li>';

            // take off a tag for disabled page.
            if(numberClass == 'disabled') {
                page = 
                    '<li class="' + numberClass + '"><span>' +
                    number + 
                    '</span></li>';
            }

            return page;
        },

        /**
         * the default search summary.
         */
        defaultSearchSummary: function(currentQuery, total,
                                       currentPage, totalPages) {
            //
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

            return resultSummary;
        },

        /**
         * default result list panel.
         */
        defaultResultList: function(strap, $result, docs,
                                    currentQuery, total, currentPage,
                                    totalPages, pagination) {

            // build the current search panel, which will include
            //  - list of items.
            //  - pagination
            var $currentSearch = 
                strap.buildCurrentSearchPanel(strap, docs, 
                        currentQuery, 
                        total, currentPage, totalPages, pagination);

            $result.html('').append($currentSearch);

            return $result;
        },

        /**
         * build the current search panel.
         */
        buildCurrentSearchPanel: function(strap, docs, currentQuery,
            total, currentPage, totalPages, pagination) {

            // panel heading...
            var heading = 
                '<div class="panel-heading">' +
                '<span>' +
                '  <strong>Order by:</strong>' +
                '  <select class="success" id="order">' +
                '    <option value="relevance">Relevance</option>' +
                '    <option value="changetime">Last Modified Date</option>' +
                '  </select>' +
                '</span>' +
                '</div>';

            // panel body
            var end = currentQuery.start + currentQuery.perPage - 1;
            end = end > total ? total : end;
            var body = '';

            // using list group for search result.
            var $ul = $('<ul class="list-group"></ul>');
            $.each(docs, function(index, item) {
                // present each item as a list group item.
                var liHtml = strap.buildMediaItemHtml(item);
                $ul.append(liHtml);
            });

            // panel footer, pagination nav bar.
            var footer = 
                '<div class="panel-footer panel-footer-custom">' +
                pagination +
                '</div>';

            var $panel = $('<div class="panel panel-info' +
                           '            panel-custom"></div>');

            // append everything together.
            $panel.append(heading).append(body)
                  .append($ul).append(footer);

            return $panel;
        },

        /**
         * the default search result filter panel.
         */
        defaultResultFilter: function(strap, $filter, facets, 
                                      currentQuery) {
        
            // panel heading...
            var heading = 
                '<div class="panel-heading">' +
                '  <strong>Search Filters</strong>' +
                '</div>';
        
            var body = 
                '<div class="panel-body">' +
                '  LIST OF FILTERS.' +
                '  <p></p>' +
                '  In Tag Cloud' +
                '</div>';
            // filter for sites
            // filter for keywords
            // filter for authors.
        
            var $panel = $('<div class="panel panel-success' +
                           '            panel-custom"></div>');
        
            // append everything together.
            $panel.append(heading).append(body);
            $filter.html('').append($panel);
        
            return $filter;
        }
    });

})(jQuery);
