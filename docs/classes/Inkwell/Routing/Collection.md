# Collection
## Collection class responsible for aggregating and mapping routes to actions

_Copyright (c) 2015, Matthew J. Sahagian_.
_Please reference the LICENSE.md file at the root of this distribution_

#### Namespace

`Inkwell\Routing`

#### Imports

<table>

	<tr>
		<th>Alias</th>
		<th>Namespace / Class</th>
	</tr>
	
	<tr>
		<td>Flourish</td>
		<td>Dotink\Flourish</td>
	</tr>
	
	<tr>
		<td>Transport</td>
		<td>Inkwell\Transport</td>
	</tr>
	
</table>

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

## Properties

### Instance Properties
#### <span style="color:#6a6e3d;">$handlers</span>

#### <span style="color:#6a6e3d;">$link</span>

#### <span style="color:#6a6e3d;">$links</span>

#### <span style="color:#6a6e3d;">$redirects</span>

#### <span style="color:#6a6e3d;">$parser</span>




## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>


<hr />

#### <span style="color:#3e6a6e;">base()</span>


<hr />

#### <span style="color:#3e6a6e;">getCompiler()</span>


<hr />

#### <span style="color:#3e6a6e;">getParser()</span>


<hr />

#### <span style="color:#3e6a6e;">handle()</span>

Handles an error with an action in the routes collection

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
				$base
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The base for all the routes
			</td>
		</tr>
					
		<tr>
			<td>
				$status
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The status string
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
				The action to call on error
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

#### <span style="color:#3e6a6e;">link()</span>


<hr />

#### <span style="color:#3e6a6e;">redirect()</span>

Redirects a route to a translation in the routes collection

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
				The route key/mapping
			</td>
		</tr>
					
		<tr>
			<td>
				$translation
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The translation to map to
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
				The type of redirect (301, 303, 307, etc...)
			</td>
		</tr>
			
	</tbody>
</table>

###### Throws

<dl>

	<dt>
					Flourish\ProgrammerException		
	</dt>
	<dd>
		in the case of conflicting routes
	</dd>

</dl>

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

#### <span style="color:#3e6a6e;">reset()</span>


<hr />

#### <span style="color:#3e6a6e;">resolve()</span>

Resolves a URL redirect


<hr />

#### <span style="color:#3e6a6e;">seek()</span>

Seeks the appropriate action for a given request


<hr />

#### <span style="color:#3e6a6e;">wrap()</span>


<hr />

#### <span style="color:#3e6a6e;">validateConflictedAction()</span>


<hr />

#### <span style="color:#3e6a6e;">validateConflictedTarget()</span>


<hr />

#### <span style="color:#3e6a6e;">validateConflictedTypes()</span>


<hr />

#### <span style="color:#3e6a6e;">match()</span>






