# ApiSwookery 🧙‍♂️

ApiSwookery is a magical tool that brews mock servers from your OpenAPI specifications. It's designed to simplify API development and testing by providing a realistic mock server that adheres to your API definition.

## Features 🌟

- Generate a fully functional mock server from your OpenAPI (formerly Swagger) specification
- Supports OpenAPI 3.0 and above
- Generate realistic mock data based on your schema definitions
- Highly configurable through command-line options and configuration files
- Supports middleware options for logging

## Installation 📦

You can install ApiSwookery via Composer:

```bash
composer global require danielebarbaro/apiswookery
```

## Usage 🚀
To brew a mock server from your OpenAPI specification, simply run the following command:

```bash
php apiswookery brew {spec} [options]
```

replace `{spec}` with the path to your OpenAPI specification file (YAML or JSON).

### Options

`--port`: Port number for the mock server (default: 9501)  
`--host`: Host for the mock server (default: 127.0.0.1)  
`--workers`: Number of worker processes (default: 4)  
`--output`: Output file for the generated server (default: openswoole-server.php)  
`--enable-logging`: Enable logging middleware  

## Examples 📚
Generate a mock server from an OpenAPI specification:

```bash
php apiswookery brew openapi.yaml
```
Enable CORS and metrics middleware:

```bash
php apiswookery brew openapi.yaml --enable-logging
```

## Testing 🧪
ApiSwookery includes a comprehensive test suite to ensure its reliability. To run the tests, use the following command:

```bash
composer test
```
## Contributing 🤝
Contributions are welcome! If you find a bug or have a feature request, please open an issue on the GitHub repository. If you'd like to contribute code, please fork the repository and submit a pull request.

## License 📄
ApiSwookery is open-sourced and it is licensed under the Apache License 2.0. See the [LICENSE](./LICENSE) file for details.

## Credits 👨‍💻
ApiSwookery is developed and maintained by Daniele Barbaro.

## Support 💖
If you find ApiSwookery helpful, please consider starring the repository on GitHub and sharing it with your colleagues and friends. Your support is greatly appreciated!

