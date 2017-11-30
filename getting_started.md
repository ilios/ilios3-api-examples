# Getting Started with the Ilios 3 API

Whether you are a developer tasked with creating reports and graphs, a tech-savvy course coordinator trying to better manage enrollments, or a student just wanting to view an easy-to-read daily list of their courses and learning materials; if you use Ilios, there will probably come a time when you need to gather information from Ilios that isn't readily available within the normal Ilios frontend interface. When this time comes, you will probably want to write a stand-alone application that accesses the Ilios 3 API via its REST interface.

We've included several example applications in this repository that can be a good starting point for any use-case and, if you are familiar with any one of the languages they're are written in, they are a great place to begin for learning how to work with the Ilios API.  If you have specific needs not available in the provided apps, you can probably edit the provided scripts fairly easily to perform the tasks you are trying to complete.

If you are not familiar with any of the languages that our example apps are written in, you can always write your own using the language of your choice, and submit it back to the repository for others to use! (We would also appreciate it!)

## The Basic Steps

Writing a separate application for accessing/adding/updating the data you need within Ilios can seem like a daunting task, but it is easier than you think!  Regardless of the programming language you choose, here are the basic steps you will need to follow in order to successfully make calls to the Ilios API.

1. Decide what information you want to get from Ilios - this is the easy part!
2. Try it in the interface! - Many developers want to automate a task that can be done manually in the Ilios user interface.  If this is the case, you should perform this task at least once in the Ilios UI.  Doing so will confirm that it can be done and, by using the Developer tools of your browser to view the network request, you can see exactly how the API request URL is formatted for being sent over the internet to Ilios.
3. Visit the API documentation at [YOUR ILIOS APPLICATION]/api/doc or visit the documentation on our demo site at https://ilios3-demo.ucsf.edu/api/doc to see what API enpoints are available to you and what information they provide.
4. Decide which endpoints you will need to use in order to access ALL of the data you will need by reviewing the Ilios 3 API Documentation thoroughly.  For example, if you are dealing with course/session user enrollments, you will probably need information retrieved from not only the `/courses` endpoint, but also `/sessions` and `/cohorts` endpoints as well.  Working with these three endpoints, and combining/relating the data they return, you should then be able to get ALL of the datapoints you need for working with user enrollments.
5. In the Ilios 3 API documentation, note the method specified for each endpoint (`GET`,`POST`,`PUT`, `DELETE`) and make sure to use that specific method within your app when it makes the call to the Ilios 3 API.
6. Programmatically construct your API request URL(s) for each endpoint! Use your browser's developer tools' `Network` tab to see how the request data should be properly formed.
7. Make the call to each endpoint using the proper filters to narrow down the request to the most specific items.
8. Combine and operate upon the data returned from each API call, and make other calls as needed.
9. Output the data as desired!

### And that's it! Following these steps basic steps should get you on your way!
