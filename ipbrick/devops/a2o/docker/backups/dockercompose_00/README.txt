1. Acesse ao link:
https://keycloak.a2o.ucoip.pt/realms/a2o/protocol/openid-connect/certs

2. Obtenha um ficheiro .json parecido com o seguinte exemplo:

{
    "keys": [
        {
            "kid": "T_y5DoG_qSYydkuc3T-URcF4j6b1uvhnP9LufPHMUkY",
            "kty": "RSA",
            "alg": "RS256",
            "use": "sig",
            "n": "oS4i5QZfeMLcyXxP2Yc_XtOpa-MwHpOkbQN7zk9LlAm1iG11xbBh60gqNZAXy25NbEhpQWhpG7AnxBT4VSX6zxaIGqzAO-PV8EsJPjvhi5V-m8sU90nTx8abXt-sB5qhw7zEZWjlpRb9H-ago98pPxqn4fkbdspK49QBlTFQ0cXIn6rhkznJqpzrrlDXt5Eclt3i7kyBqRu7APV58-dFB6mfJ6eLHkbdq-nAaGWLKW33FAq5ooVtpjAdKVo0uimjLcd13qe6ERXCr_LjYNfwgVMwirxf_5_15xtw7hpnXj5d-M5MdFLYG_EPBAEijMQBBBhoveVASuAxItTK_LJaww",
            "e": "AQAB",
            "x5c": [
                "MIIClTCCAX0CBgGVR0sJ/zANBgkqhkiG9w0BAQsFADAOMQwwCgYDVQQDDANhMm8wHhcNMjUwMjI3MTIwMjM0WhcNMzUwMjI3MTIwNDE0WjAOMQwwCgYDVQQDDANhMm8wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQChLiLlBl94wtzJfE/Zhz9e06lr4zAek6RtA3vOT0uUCbWIbXXFsGHrSCo1kBfLbk1sSGlBaGkbsCfEFPhVJfrPFogarMA749XwSwk+O+GLlX6byxT3SdPHxpte36wHmqHDvMRlaOWlFv0f5qCj3yk/Gqfh+Rt2ykrj1AGVMVDRxcifquGTOcmqnOuuUNe3kRyW3eLuTIGpG7sA9Xnz50UHqZ8np4seRt2r6cBoZYspbfcUCrmihW2mMB0pWjS6KaMtx3Xep7oRFcKv8uNg1/CBUzCKvF//n/XnG3DuGmdePl34zkx0Utgb8Q8EASKMxAEEGGi95UBK4DEi1Mr8slrDAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAH1uOIxT+bhoBZ7D4XYQ0OEa/fmR+EDaykQId9Pz2+2sXYV67huv+o2p6KgJIM9ixqoO3FtFli31XW4obl9PFt5nF3pqSHmWNn8laJOV+rDxqHTow6U1B3FY01D+ym6Y36jnvoBYlA2gmimcA4dunUc8sSBxlsazfOERlck4Rd1nROnCNcr1GjWdBJU83uJOwQmxn5t2EpWQmSJlb6aWNx7SnXanFC8kpZThHSFXgMxJWzGcmqweRUVygjWfFp/IkBpR/ov59Z25J6AxOUplAoHzmv1u5PQwsjHii4cNuZVlvQmOwYLq+cSAg3cqetARbcBaTGn5YoNcVqES2AOTOBI="
            ],
            "x5t": "QWIFHFsefARHKq3Z5jxfYOygdoI",
            "x5t#S256": "xsOtJIFyCz5bBEOuLHF0BMAwTRrOee2SrYjAgSBVIuM"
        },
        {
            "kid": "rWOLFnvLfIM-LWZSSbvpJ_JpIffrZgBOsCCxGgPq-A8",
            "kty": "RSA",
            "alg": "RSA-OAEP",
            "use": "enc",
            "n": "pUlAhJhglG8hnPrDFOLfpXB7sPyWPcxywJnYZvnNwv1hMmt0ht6XAmPcpurteXd1emA_HKgbBSqORP2GwuvNGv6WUKxTiA0ea634886MySeNn0nnOpJZbYl5vobjRRH1vQMVzPIPBzY1SmWmlhwnzR5IVVwwqZ6Ktqa554K1dj7hxLymrEjF8fZgQIVP-yTT45T1QuQsbZ_crnDhE0pWPFDd7MWDhVjJ2oiFwf_TYY6ZQ45BicZPZJvYl6hozqteLCq5NAD1CgKTk7AxJsVxpLiofofkv7vvb8h8QlGhD6ygA-GmQjnD_2dE-4Wu09_X5nqL_ovseLh_yf-oIgp0tQ",
            "e": "AQAB",
            "x5c": [
                "MIIClTCCAX0CBgGVR0sLXDANBgkqhkiG9w0BAQsFADAOMQwwCgYDVQQDDANhMm8wHhcNMjUwMjI3MTIwMjM1WhcNMzUwMjI3MTIwNDE1WjAOMQwwCgYDVQQDDANhMm8wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQClSUCEmGCUbyGc+sMU4t+lcHuw/JY9zHLAmdhm+c3C/WEya3SG3pcCY9ym6u15d3V6YD8cqBsFKo5E/YbC680a/pZQrFOIDR5rrfjzzozJJ42fSec6klltiXm+huNFEfW9AxXM8g8HNjVKZaaWHCfNHkhVXDCpnoq2prnngrV2PuHEvKasSMXx9mBAhU/7JNPjlPVC5Cxtn9yucOETSlY8UN3sxYOFWMnaiIXB/9NhjplDjkGJxk9km9iXqGjOq14sKrk0APUKApOTsDEmxXGkuKh+h+S/u+9vyHxCUaEPrKAD4aZCOcP/Z0T7ha7T39fmeov+i+x4uH/J/6giCnS1AgMBAAEwDQYJKoZIhvcNAQELBQADggEBABDDdHEjQyClqKQfLHyCX2kTbv6lL9Bg4p9VXANBbsLFTZ4ZEmxu05Z2whhaFVQNmcwVCltuUcB/2pfONqbYjfeF2W79uIVZLdX0J4xfYd1Hh4pkqYajz3cMyvVllx5WRUZs+e3mJObyqcAkHc4A5EO8/P2B5IpqDepRl8JOmbOX/LoiY8EqPt1WemqTqkaZ2cFLFRp3lNj/rC3LZa3ZY4dmA8VnbLbYjLzVsES8y9hdGCDJ0sIZLXlJNsQUDTMIYMZlwMbs2SH5/p5sUpvT5sJEOe26m4sHDLGFFrBBZoTztWkMevlUIjbF8imnMGSDllWroAVip6VYNzOU1bqiOwE="
            ],
            "x5t": "KkYV5EiDApBX0HFyQreRzJfUBQA",
            "x5t#S256": "hV6vFHX0v6aVuQiJ1ysJItwTkRdB9vnYuf0Lt2c0984"
        }
    ]
}