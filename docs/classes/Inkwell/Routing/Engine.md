# Engine
## The main routing engine which runs routing operations over a collection

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
		<td>Closure</td>
		<td>Closure</td>
	</tr>
	
	<tr>
		<td>Exception</td>
		<td>Exception</td>
	</tr>
	
	<tr>
		<td>Event</td>
		<td>Inkwell\Event</td>
	</tr>
	
	<tr>
		<td>Transport</td>
		<td>Inkwell\Transport</td>
	</tr>
	
	<tr>
		<td>Flourish</td>
		<td>Dotink\Flourish</td>
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
#### <span style="color:#6a6e3d;">$actions</span>

#### <span style="color:#6a6e3d;">$collection</span>

#### <span style="color:#6a6e3d;">$params</span>

#### <span style="color:#6a6e3d;">$response</span>

#### <span style="color:#6a6e3d;">$request</span>

#### <span style="color:#6a6e3d;">$resolver</span>

#### <span style="color:#6a6e3d;">$restless</span>




## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>


<hr />

#### <span style="color:#3e6a6e;">anchor()</span>


<hr />

#### <span style="color:#3e6a6e;">defer()</span>


<hr />

#### <span style="color:#3e6a6e;">demit()</span>


<hr />

#### <span style="color:#3e6a6e;">getAction()</span>


<hr />

#### <span style="color:#3e6a6e;">getEntryAction()</span>


<hr />

#### <span style="color:#3e6a6e;">isAction()</span>


<hr />

#### <span style="color:#3e6a6e;">isEntryAction()</span>


<hr />

#### <span style="color:#3e6a6e;">redirect()</span>


<hr />

#### <span style="color:#3e6a6e;">rewrite()</span>


<hr />

#### <span style="color:#3e6a6e;">run()</span>


<hr />

#### <span style="color:#3e6a6e;">setMutable()</span>


<hr />

#### <span style="color:#3e6a6e;">setRestless()</span>

Sets the router to restless mode (will try / and non-/ URLs)

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
				$restless
			</td>
			<td>
									<a href="http://www.php.net/language.types.boolean.php">boolean</a>
				
			</td>
			<td>
				TRUE to try both URL forms, FALSE to only accept what is given
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

#### <span style="color:#3e6a6e;">exec()</span>

Executes a resolved action

##### Details

This function modifies the response directly and should be expected to mutate the
output based on the action.

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
				$action
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				A callable action
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

#### <span style="color:#3e6a6e;">mapAction()</span>

Maps an action


<hr />

#### <span style="color:#3e6a6e;">mapRewrite()</span>


<hr />

#### <span style="color:#3e6a6e;">resolve()</span>

Resolve an action using the registered resolver

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
				$action
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The unresolved action
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
		If unresolved non-closure is passed without resolver
	</dd>

</dl>

###### Returns

<dl>
	
		<dt>
			mixed
		</dt>
		<dd>
			The resolved action (a valid callback)
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">runHandler()</span>


<hr />

#### <span style="color:#3e6a6e;">runAction()</span>

Runs the current action

##### Details

This will demit when completed causing the action chain to break.

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>






