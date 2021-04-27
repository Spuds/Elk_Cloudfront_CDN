## CloudFront CDN for ElkArte

### License
This addon is released under a MPL V1.1 license, a copy of it with its provisions is included with the package.

### Introduction
This addon will set some of your sites resources to point to an Amazon Cloud front Distribution.  This allows you to point
theme images, CSS, JS and Custom avatar images to come from a distribution domain, such that those items are cached and
served from edge servers in the cloud.

This will speed up distribution of your static web content, such as .js, .css, and image files, to your users. CloudFront
delivers your content through a worldwide network of data centers called edge locations. When a user requests content
that you're serving with CloudFront, the user is routed to the edge location that provides the lowest latency (time delay),
so that content is delivered with the best possible performance. If the content is already in the edge location with the
lowest latency, CloudFront delivers it immediately. If the content is not in that edge location, CloudFront retrieves it
from an Amazon S3 bucket or an HTTP server (for example, a web server) that you have identified as the source for the
definitive version of your content.

### Features
 - Works without making any theme edits so should work with all themes and addons.
 - Adds a few check boxes in the admin panel where you can choose what items to serve from the cloud.
