import urllib.request
import urllib.error
import concurrent.futures
import time

def executar_teste_carga(url_alvo: str, total_requests: int, concurrency: int) -> dict:
    def fetch(url):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Gerador-Carga/1.0'})
            with urllib.request.urlopen(req, timeout=5) as response:
                return str(response.getcode())
        # Captura erro HTTP normal (ex: 404, 500)
        except urllib.error.HTTPError as e:
            return f"HTTP {e.code}"
        # Captura erro de Rede/Docker (ex: Não achou o Nginx, Conexão Recusada)
        except urllib.error.URLError as e:
            return f"Erro de Rede: {e.reason}"
        # Qualquer outro erro bizarro
        except Exception as e:
            return f"Erro Desconhecido: {str(e)}"

    start_time = time.time()
    status_codes = {}

    with concurrent.futures.ThreadPoolExecutor(max_workers=concurrency) as executor:
        futures = [executor.submit(fetch, url_alvo) for _ in range(total_requests)]
        for future in concurrent.futures.as_completed(futures):
            code = future.result()
            status_codes[code] = status_codes.get(code, 0) + 1

    return {
        "tempo_total_segundos": round(time.time() - start_time, 2),
        "status_codes": status_codes,
        "total_disparado": total_requests
    }