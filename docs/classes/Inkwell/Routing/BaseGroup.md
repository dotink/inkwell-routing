# BaseGroup
## Simple proxy to group Collection calls under a common base

_Copyright (c) 2015, Matthew J. Sahagian_.
_Please reference the LICENSE.md file at the root of this distribution_

### Details

This is a proxy class which calls certain methods on the Collection class using a store
and common base with each call reducing the need to repeat the base in manual configuration.

#### Namespace

`Inkwell\Routing`

#### Authors

<table>
	<thead>
		<th>Name</th>
		<th>Handle</th>
		<th>Email</th>
	</thead>
	<tbody>
	
		<tr>
			<td>
				Matthew J. Sahagian
			</td>
			<td>
				mjs
			</td>
			<td>
				msahagian@dotink.org
			</td>
		</tr>
	
	</tbody>
</table>


## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>

Create a new BaseGroup

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$collection
			</td>
			<td>
									<a href="./Collection.md">Collection</a>
				
			</td>
			<td>
				The collection we'll be adding to
			</td>
		</tr>
					
		<tr>
			<td>
				$base
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The base route which added handlers, links, and redirects are under
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">handle()</span>

Add and error handler to the collection

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$status
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The status response to match
			</td>
		</tr>
					
		<tr>
			<td>
				$action
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				A resolvable action for the registered resolver
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			BaseGroup
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">link()</span>

Add a link between a route and a particular action to the collection

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$route
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The route to match
			</td>
		</tr>
					
		<tr>
			<td>
				$action
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				A resolvable action for the registered resolver
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			BaseGroup
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">redirect()</span>

Add a redirect between a route and a new target

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$route
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The route to match
			</td>
		</tr>
					
		<tr>
			<td>
				$target
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The new route to seek out
			</td>
		</tr>
					
		<tr>
			<td>
				$type
			</td>
			<td>
									<a href="http://www.php.net/language.types.integer.php">integer</a>
				
			</td>
			<td>
				The type of redirection by code
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			BaseGroup
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>






