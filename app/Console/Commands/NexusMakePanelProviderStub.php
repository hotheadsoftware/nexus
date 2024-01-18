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

class NexusMakePanelProviderStub extends Command
{
    protected $signature = 'nexus:make-panel-provider-stub
                        {--name= : The name of the panel}
                        {--tenant= : Specify if this is a tenant panel}
                        {--model= : The model used for authentication}
                        {--login= : Indicate if the panel should have a login form}
                        {--registration= : Indicate if user registration is an option}
                        {--branding= : Specify if custom branding should be enabled}
                        {--copy_branding= : Indicate if existing panel branding should be copied}
                        {--copy_branding_from= : The name of the panel to copy branding from}
                        {--api_tokens= : Indicate if users need to generate API tokens}';

    protected $description = 'Updates the PanelProvider stub file with the desired configuration.';

    protected ?Collection $configuration = null;

    // Abstract Syntax Tree for PHP Parsing.
    // Enables more reliable file modification than string replacement.
    protected array $ast = [];

    public static string $stubFileDir = 'storage/app/nexus/stubs';

    public static string $stubFilePath = 'vendor/filament/support/stubs/PanelProvider.stub';

    public static string $stubFilePathOld = 'storage/app/nexus/stubs/PanelProvider.stub.old';

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

    public function handle(): int|string
    {
        File::isDirectory(static::$stubFileDir) || File::makeDirectory(static::$stubFileDir, 0755, true, true);
        File::copy(static::$stubFilePath, static::$stubFilePathOld);

        $originalContent    = $this->getStubFile();
        $originalClassName  = '{{ class }}';
        $temporaryClassName = 'StubClassName';
        $panelIdToken       = '{{ id }}';

        try {

            $this->configuration = Nexus::getPanelConfigurationInputs($this);
            $this->validateUniquePanelName($this->configuration->get('name'));

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

            $printer = new Standard();
            $content = $printer->prettyPrintFile($this->ast);
            $content = str_replace($temporaryClassName, $originalClassName, $content);
            $content = $this->setCustomBranding($content);
            $content = $this->escapeBackSlashes($content);

            $this->setStubFile($content);

        } catch (Exception $e) {
            $this->call('nexus:revert-panel-provider-stub');
            $this->error($e->getMessage());
            throw $e;
        }

        return $this::$stubFilePath;
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
        if (! File::exists($this::$stubFilePath)) {
            $this->error('File not found!');
            exit;
        }

        return File::get($this::$stubFilePath);
    }

    protected function setStubFile($content): string
    {
        if (! File::exists($this::$stubFilePath)) {
            $this->error('File not found!');
            exit;
        }

        File::put($this::$stubFilePath, $content);

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

    protected function validateUniquePanelName(string $name): void
    {
        if (Nexus::panelNames()->contains($name)) {
            $this->error('A panel with that name already exists.');
            exit;
        }
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

    protected function setCustomBrandingLocationComment(): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): void
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
}
