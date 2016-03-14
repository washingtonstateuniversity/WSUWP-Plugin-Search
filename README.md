# WSUWP Search Plugin

[![Build Status](https://travis-ci.org/washingtonstateuniversity/WSUWP-Plugin-Search.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/WSUWP-Plugin-Search)

Provides a connection to [WSU Search](https://github.com/washingtonstateuniversity/wsu-search/) from WordPress sites at WSU.

* By default, posts and pages of public sites are indexed to the `wsu-web` index with a type of `page`.
* Restricted sites must also have the `index_private_site` option set to `1` to index content.
* If `wsuwp_search_development` is filtered to `true`, `-dev` will be appended to index URLs.

## Document Structure

The current document structure for pages saved by the WSU Search plugin is:

```
{
	"body" : {
		"title":               "Title of the page or post",
		"date":                "2014-07-18 21:38:27",
		"author":              "Jeremy Felt",
		"content":             "Content of the page or post",
		"url":                 "http:\/\/wp.wsu.edu\/2014\/07\/18\/another-test-post\/",
		"generator":           "wsuwp",
		"site_id":             52,
		"hostname":            "news.wsu.dev",
		"site_url":            "news.wsu.dev",
		"network_id":          8,
		"site_category":       [ "uncategorized" ],
		"university_tag":      ["tag"],
		"university_category": [ "academic-subjects" ],
		"university_location": [ "wsu-extension" ]
	}
}
```
