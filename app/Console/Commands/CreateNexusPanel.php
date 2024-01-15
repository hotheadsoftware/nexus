<?php

namespace App\Console\Commands;

use App\Facades\Nexus;
use Exception;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Class CreateNexusPanel
 *
 * The goal here is to rewrite the Filament PanelProvider.stub file with the defaults we want.
 * This will allow a user to quickly wire up a new panel without a lot of manual work.
 *
 * TODO - Current Status: This script successfully rewrites the PanelProvider.stub file and
 * triggers creation of a new Panel.
 *
 * We still need to complete numerous steps to make this nearly automatic:
 *
 * 2. Create a new Model using the panel name (ucfirst).
 * 3. Create a new Migration using the panel name (ucfirst).
 * 4. Move the migration into the tenant subfolder.
 * 5. Add the new Model to the tenant UserSeeder.
 * 6. Add a new auth guard & driver based on the model name.
 * 7. Write new KEY/VALUE pairs to the .env file.
 * 8. Write new config values to the config/panels.php file.
 */
class CreateNexusPanel extends Command
{
    protected $signature = 'nexus:create-panel
                        {--name= : The name of the panel}
                        {--tenant= : Specify if this is a tenant panel}
                        {--model= : The model used for authentication}
                        {--login= : Indicate if the panel should have a login form}
                        {--registration= : Indicate if user registration is an option}
                        {--branding= : Specify if custom branding should be enabled}
                        {--copy_branding= : Indicate if existing panel branding should be copied}
                        {--copy_branding_from= : The name of the panel to copy branding from}
                        {--api_tokens= : Indicate if users need to generate API tokens}';

    protected $description = 'Command description';

    protected ?Collection $configuration = null;

    // Abstract Syntax Tree for PHP Parsing.
    // Enables more reliable file modification than string replacement.
    protected array $ast = [];

    protected string $stubFilePath = 'vendor/filament/support/stubs/PanelProvider.stub';

    protected function setCustomBrandingLocationComment(): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node)
            {
                if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                    // Create a comment node
                    $comment = new Comment('# Custom Branding Goes Here');

                    // Attach the comment to the 'panel' method
                    $node->setAttribute('comments', array_merge($node->getAttribute('comments', []), [$comment]));
                }
            }
        });

        $this->ast = $traverser->traverse($this->ast);
    }

    protected function escapeBackslashes(string $content): string
    {
        $find = [
            'App\\Filament\\{{ directory }}\\Resources',
            'App\\Filament\\{{ directory }}\\Pages',
            'App\\Filament\\{{ directory }}\\Widgets',
        ];

        $replace = [
            'App\\\\Filament\\\\\'.ucfirst(self::PANEL).\'\\\\Resources',
            'App\\\\Filament\\\\\'.ucfirst(self::PANEL).\'\\\\Pages',
            'App\\\\Filament\\\\\'.ucfirst(self::PANEL).\'\\\\Widgets',
        ];

        return str_replace($find, $replace, $content);
    }

    public function handle(): void
    {
        // Retain this - we'll use it to restore the file at the end.
        $originalContent    = $this->getStubFile();
        $originalClassName  = '{{ class }}';
        $temporaryClassName = 'StubClassName';
        $panelIdToken       = '{{ id }}';

        try {

            $this->configuration = $this->getConfiguration();

            $content   = str_replace($originalClassName, $temporaryClassName, $originalContent);
            $content   = str_replace('/{{ directory }}/', '/\'.ucfirst(self::PANEL).\'/', $content);
            $parser    = (new ParserFactory())->createForNewestSupportedVersion();
            $this->ast = $parser->parse($content);

            $this->setImports();
            $this->ensureConstantExists('PANEL', $panelIdToken);
            $this->replaceMethodArguments('id');
            $this->replaceMethodArguments('path');

            $this->setLogin();
            $this->setRegistration();
            $this->setAuthGuard();
            $this->setSpa();
            $this->setMiddleware();
            $this->setApiTokens();
            $this->setCustomBrandingLocationComment();

            // Write the AST to the Stub file.
            $prettyPrinter = new Standard();
            $content       = $prettyPrinter->prettyPrintFile($this->ast);
            $content       = str_replace($temporaryClassName, $originalClassName, $content);
            $content       = $this->setCustomBranding($content);
            $content       = $this->escapeBackSlashes($content);

            $this->setStubFile($content);

            $this->call('make:filament-panel', [
                'id' => $this->ask('What is the ID?'),
            ]);

            // Create the auth guard
            // Create the auth provider
            // Create the Model
            // Create the migration & move it to the tenant folder
            // Populate the migration with the field contents from the user migration
            // Add the .env & .env.example variables
            // Add the config/panels.php references
            // The tenant/UserSeeder needs to be updated to include the new Model
            // Call artisan tenant:seed to create new users for each tenant
            // Write a tenant seeder to ensure that all panels have brands in 1:1 for each tenant.

        } catch (Exception $e) {
            $this->error($e->getMessage());
        } finally {
            $this->setStubFile($originalContent);
        }

        $this->setStubFile($originalContent);
    }

    protected function setCustomBranding(string $content): string
    {
        if (! $this->configuration->get('branding')) {
            return $content;
        }

        $find = '# Custom Branding Goes Here';

        return str_replace($find, "    public function register(): void
    {
        parent::register();

        \$this->app->afterResolving(DatabaseTenancyBootstrapper::class, function () {
            tenant()?->brands->where('panel', self::PANEL)?->first()?->applyToPanel(self::PANEL, tenant());
        });
    }", $content);
    }

    protected function getStubFile(): string
    {
        if (! File::exists($this->stubFilePath)) {
            $this->error('File not found!');
            exit;
        }

        return File::get($this->stubFilePath);
    }

    protected function setStubFile($content): string
    {
        if (! File::exists($this->stubFilePath)) {
            $this->error('File not found!');
            exit;
        }

        File::put($this->stubFilePath, $content);

        return $this->getStubFile();
    }

    protected function getImports(): Collection
    {
        $imports = collect([
            'Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper',
            'Stancl\Tenancy\Middleware\InitializeTenancyByDomain',
            'Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains',
        ]);

        if ($this->configuration->get('api_tokens')) {
            $imports->add('Jeffgreco13\FilamentBreezy\BreezyCore');
        }

        return $imports;
    }

    protected function setImports(): void
    {
        foreach ($this->getImports() as $import) {
            $this->ensureUseStatementExists($import);
        }
    }

    protected function setLogin(): void
    {
        if ($this->configuration->get('login')) {
            $this->ensureMethodCallExists('login');
        }
    }

    protected function setRegistration(): void
    {
        if ($this->configuration->get('registration')) {
            $this->ensureMethodCallExists('registration');
        }
    }

    protected function setAuthGuard(): void
    {
        $this->ensureMethodCallExists('authGuard', ['self::PANEL']);
    }

    protected function setSpa(): void
    {
        $this->ensureMethodCallExists('spa');
    }

    protected function setMiddleware(): void
    {
        if ($this->configuration->get('tenant')) {
            $middlewareToAdd = [
                'InitializeTenancyByDomain',
                'PreventAccessFromCentralDomains',
            ];

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new class($middlewareToAdd) extends NodeVisitorAbstract
            {
                public function __construct(public array $middlewareToAdd)
                {
                }

                public function enterNode(Node $node): ?Node
                {
                    if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                        foreach ($node->stmts as $stmt) {
                            if ($stmt instanceof Return_ && $stmt->expr instanceof MethodCall) {
                                $methodCall = $stmt->expr;
                                while ($methodCall instanceof MethodCall) {
                                    if ($methodCall->name->toString() === 'middleware') {
                                        $middlewareArg = $methodCall->args[0]->value;
                                        if (! $middlewareArg instanceof Array_) {
                                            $middlewareArg              = new Array_();
                                            $methodCall->args[0]->value = $middlewareArg;
                                        }
                                        foreach (array_reverse($this->middlewareToAdd) as $middleware) {
                                            array_unshift($middlewareArg->items, new ArrayItem(
                                                new ClassConstFetch(new Name($middleware), new Identifier('class'))
                                            ));
                                        }
                                        break;
                                    }
                                    $methodCall = $methodCall->var;
                                }
                            }
                        }
                    }

                    return null;
                }
            });

            $this->ast = $traverser->traverse($this->ast);
        }
    }

    protected function getInput(
        string $info,
        string $label,
        string $placeholder,
        string $type,
        string $default = ''
    ): string {
        $this->info($info);

        return match ($type) {
            'bool'  => select(label: $label, options: ['Yes', 'No'], default: 'Yes', required: true) == 'Yes',
            default => text(label: $label, placeholder: $placeholder, default: $default, required: true),
        };

    }

    protected function validateUniquePanelName(string $name): void
    {
        if (Nexus::panelNames()->contains($name)) {
            $this->error('A panel with that name already exists.');
            exit;
        }
    }

    protected function getConfiguration(): Collection
    {
        $config = collect();

        $config->put('name', $this->option('name') ?? $this->getInput(
            info: "We'll use the Panel ID as the name and URL path.",
            label: "What's the name of this panel?",
            placeholder: 'App',
            type: 'text',
        ));

        $this->validateUniquePanelName($config->get('name'));

        $config->put('tenant', $this->option('tenant') ?? $this->getInput(
            info: 'If this is a tenant panel, any created items will be namespaced under tenant or Tenant.',
            label: 'Is this a tenant panel?',
            placeholder: 'Yes',
            type: 'bool',
        ));

        $config->put('model', $this->option('model') ?? $this->getInput(
            info: "If the model doesn't exist, we'll create it under the appropriate Namespace.",
            label: 'Which model will be used for Authenticating users?',
            placeholder: 'User',
            type: 'text',
        ));

        $config->put('login', $this->option('login') ?? $this->getInput(
            info: 'Some panels may not need a login form; for example, if your existing logged-in users will be using panel-switching.',
            label: 'Should this panel have a login form?',
            placeholder: 'Yes',
            type: 'bool',
        ));

        $config->put('registration', $this->option('registration') ?? $this->getInput(
            info: 'Choose Yes if registration should *ever* be an option on this panel. You can enable/disable from the interface.',
            label: 'Should user registration be an option?',
            placeholder: 'Yes',
            type: 'bool',
        ));

        $config->put('branding', $this->option('branding') ?? $this->getInput(
            info: 'Custom Branding is managed from the Account panel.',
            label: 'Should custom branding be enabled?',
            placeholder: 'Yes',
            type: 'bool',
        ));

        if ($config->get('branding')) {
            $config->put('copy_branding', $this->option('copy_branding') ?? $this->getInput(
                info: "If you've already customized another panel, we can use the branding from that panel.",
                label: "Should we copy an existing panel's custom branding?",
                placeholder: 'Yes',
                type: 'bool',
            ));
        }

        if ($config->get('copy_branding')) {
            $config->put('copy_branding_from', $this->option('copy_branding_from') ?? $this->getInput(
                info: 'Ok, which panel should we copy from?',
                label: 'Enter the name of an existing panel with custom branding:',
                placeholder: 'App',
                type: 'text',
            ));
        }

        $config->put('api_tokens', $this->option('api_tokens') ?? $this->getInput(
            info: "Note: If sharing auth models and another panel already allows token generation, this isn't needed.",
            label: 'Will users of this panel need to generate API tokens?',
            placeholder: 'Yes',
            type: 'bool',
        ));

        return $config;
    }

    protected function ensureMiddlewareExists(): void
    {
        $middlewareToAdd = [
            'PreventAccessFromCentralDomains::class',
            'InitializeTenancyByDomain::class',
        ];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($middlewareToAdd) extends NodeVisitorAbstract
        {
            public function __construct(public array $middlewareToAdd)
            {
            }

            public function enterNode(Node $node): ?Node
            {
                if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                    $foundMiddlewareCall = false;

                    foreach ($node->stmts as $stmt) {
                        if ($stmt instanceof MethodCall && $stmt->name->toString() === 'middleware') {
                            $foundMiddlewareCall = true;

                            if (isset($stmt->args[0]) && $stmt->args[0]->value instanceof Node\Expr\Array_) {
                                $array = $stmt->args[0]->value;
                            } else {
                                $array      = new Node\Expr\Array_();
                                $stmt->args = [new Arg($array)];
                            }

                            foreach ($this->middlewareToAdd as $middleware) {
                                array_unshift($array->items, new Node\Expr\ArrayItem(
                                    new ClassConstFetch(new Name($middleware), new Identifier('class'))
                                ));
                            }
                            break;
                        }
                    }

                    if (! $foundMiddlewareCall) {
                        $middlewareArray = new Node\Expr\Array_();
                        foreach ($this->middlewareToAdd as $middleware) {
                            $middlewareArray->items[] = new Node\Expr\ArrayItem(
                                new ClassConstFetch(new Name($middleware), new Identifier('class'))
                            );
                        }
                        $node->stmts[] = new MethodCall(new Variable('panel'), 'middleware', [new Arg($middlewareArray)]);
                    }
                }

                return null;
            }
        });

        $this->ast = $traverser->traverse($this->ast);
    }

    protected function setApiTokens(): void
    {
        if (! $this->configuration->get('api_tokens')) {
            return;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): ?Node
            {
                if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                    // Construct the nested method calls
                    $breezyCoreMakeCall = new StaticCall(
                        new Name('BreezyCore'),
                        'make'
                    );

                    $myProfileCall = new MethodCall(
                        $breezyCoreMakeCall,
                        'myProfile',
                        [
                            new Arg(
                                new Node\Expr\ConstFetch(new Name('false')),
                                false,
                                false,
                                [],
                                new Identifier('shouldRegisterNavigation')
                            ),
                        ]
                    );

                    $enableSanctumTokensCall = new MethodCall(
                        $myProfileCall,
                        'enableSanctumTokens',
                        [
                            new Arg(
                                new Node\Expr\Array_([
                                    new Node\ArrayItem(new Node\Scalar\String_('create')),
                                    new Node\ArrayItem(new Node\Scalar\String_('create')),
                                    new Node\ArrayItem(new Node\Scalar\String_('read')),
                                    new Node\ArrayItem(new Node\Scalar\String_('update')),
                                    new Node\ArrayItem(new Node\Scalar\String_('delete')),
                                ]),
                                false,
                                false,
                                [],
                                new Identifier('permissions')
                            ),
                        ]
                    );

                    // Modify the existing return statement
                    foreach ($node->stmts as $stmt) {
                        if ($stmt instanceof Return_) {
                            // Assume $stmt->expr is the current method chain. Modify it to add the plugins method call.
                            $stmt->expr = new MethodCall(
                                $stmt->expr,
                                'plugins',
                                [new Arg(new Node\Expr\Array_([new Node\ArrayItem($enableSanctumTokensCall)]))]
                            );
                            break;
                        }
                    }

                    return $node;
                }

                return null;
            }
        });

        $this->ast = $traverser->traverse($this->ast);
    }

    protected function ensureUseStatementExists(string $target): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($target) extends NodeVisitorAbstract
        {
            public function __construct(public string $target)
            {
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Namespace_) {
                    $newUses = [
                        new Use_([new UseUse(new Name($this->target))]),
                    ];

                    array_splice($node->stmts, 1, 0, $newUses);
                }
            }
        });
        $this->ast = $traverser->traverse($this->ast);
    }

    protected function ensureMethodCallExists($method, $args = []): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($method, $args) extends NodeVisitorAbstract
        {
            public function __construct(public string $method, public array $args = [])
            {
            }

            public function leaveNode(Node $node)
            {
                if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                    foreach ($node->stmts as $stmt) {
                        if ($stmt instanceof Node\Stmt\Return_ && $stmt->expr instanceof MethodCall) {
                            $current = $stmt->expr;
                            while ($current instanceof MethodCall) {
                                if ($current->name->toString() === $this->method) {
                                    return null;
                                }
                                $current = $current->var;
                            }

                            $args = [];
                            foreach ($this->args as $arg) {
                                $args[] = new Arg(new ClassConstFetch(new Name('self'), new Identifier('PANEL')));
                            }

                            $stmt->expr = new MethodCall($stmt->expr, $this->method, $args);
                        }
                    }
                }
            }
        });
        $this->ast = $traverser->traverse($this->ast);
    }

    protected function ensureConstantExists(string $name, $value): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($name, $value) extends NodeVisitorAbstract
        {
            public function __construct(public string $name, public $value)
            {
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Class_) {
                    $hasPanelConstant = false;

                    foreach ($node->stmts as $stmt) {
                        if ($stmt instanceof ClassConst) {
                            foreach ($stmt->consts as $const) {
                                if ($const->name->toString() === $this->name) {
                                    $hasPanelConstant = true;
                                    break;
                                }
                            }
                        }
                    }

                    if (! $hasPanelConstant) {
                        // Create the PANEL constant node
                        $panelConst = new ClassConst([
                            new Const_($this->name, new String_($this->value)),
                        ], Modifiers::PUBLIC);

                        // Insert the constant at the beginning of the class statements
                        array_unshift($node->stmts, $panelConst);
                    }
                }
            }
        });
        $this->ast = $traverser->traverse($this->ast);
    }

    protected function replaceMethodArguments($method): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($method) extends NodeVisitorAbstract
        {
            public function __construct(public string $method, public array $args = [])
            {
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof ClassMethod && $node->name->toString() === 'panel') {
                    $this->traverseMethodCalls($node->stmts);
                }
            }

            private function traverseMethodCalls(array $stmts): void
            {
                foreach ($stmts as $stmt) {
                    if ($stmt instanceof Node\Stmt\Return_ && $stmt->expr instanceof MethodCall) {
                        $current = $stmt->expr;
                        while ($current instanceof MethodCall) {
                            $methodName = $current->name->toString();
                            if ($methodName == $this->method) {
                                $current->args = [new Arg(new ClassConstFetch(new Name('self'), new Identifier('PANEL')))];
                            }
                            $current = $current->var;
                        }
                    }
                }
            }
        }
        );
        $this->ast = $traverser->traverse($this->ast);
    }
}
