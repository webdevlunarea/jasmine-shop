<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    #container-isi>div:nth-child(odd) {
        background-color: var(--hijaumuda2);
    }
</style>
<div class="konten">
    <form method="post" action="/editarticle/<?= $artikel['id']; ?>" enctype="multipart/form-data">
        <div class="container">
            <h1 class="mb-3">Edit Artikel</h1>
            <?= csrf_field(); ?>
            <div>
                <table class="table-input w-100">
                    <tbody>
                        <tr>
                            <td>Judul</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="judul" required value="<?= $artikel['judul']; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Penulis</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="penulis" required value="<?= $artikel['penulis']; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>
                                <div class="baris d-flex gap-1">
                                    <select name="kategori-barang" class="form-select">
                                        <option value="lemari dewasa" <?= explode(',', $artikel['kategori'])[0] == 'lemari dewasa' ? 'selected' : ''; ?>>Lemari Dewasa</option>
                                        <option value="lemari anak" <?= explode(',', $artikel['kategori'])[0] == 'lemari anak' ? 'selected' : ''; ?>>Lemari Anak</option>
                                        <option value="meja rias" <?= explode(',', $artikel['kategori'])[0] == 'meja rias' ? 'selected' : ''; ?>>Meja Rias</option>
                                        <option value="meja belajar" <?= explode(',', $artikel['kategori'])[0] == 'meja belajar' ? 'selected' : ''; ?>>Meja Belajar</option>
                                        <option value="meja tv" <?= explode(',', $artikel['kategori'])[0] == 'meja tv' ? 'selected' : ''; ?>>Meja TV</option>
                                        <option value="meja tulis" <?= explode(',', $artikel['kategori'])[0] == 'meja tulis' ? 'selected' : ''; ?>>Meja Tulis</option>
                                        <option value="meja komputer" <?= explode(',', $artikel['kategori'])[0] == 'meja komputer' ? 'selected' : ''; ?>>Meja Komputer</option>
                                        <option value="rak sepatu" <?= explode(',', $artikel['kategori'])[0] == 'rak sepatu' ? 'selected' : ''; ?>>Rak Sepatu</option>
                                        <option value="rak besi" <?= explode(',', $artikel['kategori'])[0] == 'rak besi' ? 'selected' : ''; ?>>Rak Besi</option>
                                        <option value="rak serbaguna" <?= explode(',', $artikel['kategori'])[0] == 'rak serbaguna' ? 'selected' : ''; ?>>Rak Serbaguna</option>
                                        <option value="kursi" <?= explode(',', $artikel['kategori'])[0] == 'kursi' ? 'selected' : ''; ?>>Kursi</option>
                                    </select>
                                    <select name="kategori" class="form-select">
                                        <option value="edukasi" <?= explode(',', $artikel['kategori'])[1] == 'edukasi' ? 'selected' : ''; ?>>Edukasi</option>
                                        <option value="tips & trik" <?= explode(',', $artikel['kategori'])[1] == 'tips & trik' ? 'selected' : ''; ?>>Tips & Trik</option>
                                        <option value="rekomendasi" <?= explode(',', $artikel['kategori'])[1] == 'rekomendasi' ? 'selected' : ''; ?>>Rekomendasi</option>
                                        <option value="plus minus" <?= explode(',', $artikel['kategori'])[1] == 'plus minus' ? 'selected' : ''; ?>>Plus & Minus</option>
                                    </select>
                                </div>
                            </td>
                        <tr>
                            <td>Tanggal Ubah</td>
                            <td>
                                <div class="baris"><input type="datetime-local" class="form-control" name="waktu" required value="<?= $waktu; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Gambar Header</td>
                            <td>
                                <div class="baris"><input type="file" class="form-control" name="header">
                                </div>
                            </td>
                        </tr>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="container mt-3">
            <h5>Isi Artikel</h5>
        </div>
        <div id="container-react" class="d-flex flex-column gap-2"></div>
    </form>
</div>

<script
    src="https://unpkg.com/react@17/umd/react.development.js"
    crossorigin></script>
<script
    src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"
    crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Skrip JavaScript untuk React dengan Babel -->
<?php if ($isiJson) { ?>
    <script type="text/babel">
        const { useState, useEffect } = React;
        const App = () => {
            const [arr, setArr] = useState(JSON.parse('<?= $isiJson; ?>'));
            const [arrCounterElm, setArrCounterElm] = useState("<?= $arrCounter; ?>");
            const [selectTag, setSelectTag] = useState('')

            const geserAtas = (index) => {
                if (index === 0) return;
                const newArr = [...arr];
                [newArr[index - 1], newArr[index]] = [
                    newArr[index],
                    newArr[index - 1],
                ];
                setArr(newArr);
            };
            const geserBawah = (index) => {
                if (index === arr.length - 1) return;
                const newArr = [...arr];
                [newArr[index], newArr[index + 1]] = [
                    newArr[index + 1],
                    newArr[index],
                ];
                setArr(newArr);
            };
            const hapusIsi = (index) => {
                const newArr = [...arr];
                newArr.splice(index, 1);
                setArr(newArr);
            };
            const tambahIsi = (ind = -1) => {
                let tagBaru = {}
                if(selectTag) {
                    if(selectTag == 'h2' || selectTag == 'h4' || selectTag == 'p') {
                        tagBaru = {
                            tag: selectTag,
                            teks: '',
                            style: ''
                        }
                    } else if (selectTag == 'a') {
                        tagBaru = {
                            tag: selectTag,
                            link: '',
                            teks: '',
                            style: 'd-inline link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover'
                        }
                    } else if (selectTag == 'space') {
                        tagBaru = {
                            tag: selectTag,
                        }
                    } else if (selectTag == 'img') {
                        tagBaru = {
                            tag: selectTag,
                            style: ''
                        }
                    } else if (selectTag == 'div') {
                        tagBaru = {
                            tag: selectTag,
                            style: '',
                            children: []
                        }
                    }

                    //cek apakahan tambahisi dari utama atau div
                    if(ind >= 0) {
                        const newArr = [...arr];
                        newArr[ind].children.push(tagBaru)
                        setArr(newArr);
                    } else {
                        setArr([...arr, tagBaru]);
                    }
                }
            }
            const handleChangeInputIsi = (isi, key, ind) => {
                const newArr = [...arr];
                newArr[ind][key] = isi;
                setArr(newArr)
            }

            useEffect(() => {
                let arrCounter = [];
                for (let i = 1; i <= arr.length; i++) {
                    arrCounter.push(i);
                }
                setArrCounterElm(arrCounter.join(","));
            }, [arr]);

            return (
                <>
                <div id="container-isi" className="d-flex flex-column gap-2">
                    {arr.map((obj, ind_obj) => {
                        if (
                            obj.tag == "p" ||
                            obj.tag == "h2" ||
                            obj.tag == "h4"
                        ) {
                            return (
                            <div className="py-3" key={ind_obj}>    
                                <div className="container">
                                    <input
                                        type="text"
                                        name={"tag" + (ind_obj + 1)}
                                        value={obj.tag}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                        }}
                                    />
                                    <textarea
                                        className="w-100 mt-1"
                                        placeholder="teks"
                                        name={"teks" + (ind_obj + 1)}
                                        value={obj.teks}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'teks', ind_obj)
                                        }}
                                    ></textarea>
                                    <input
                                        type="text"
                                        name={"style" + (ind_obj + 1)}
                                        placeholder="style"
                                        value={obj.style}
                                        className="mb-1"
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                        }}
                                    />
                                    <div className="d-flex gap-1">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                hapusIsi(ind_obj);
                                            }}
                                        >
                                            hapus
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                geserAtas(ind_obj);
                                            }}
                                        >
                                            <i className="material-icons m-0">
                                                expand_less
                                            </i>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                geserBawah(ind_obj);
                                            }}
                                        >
                                            <i className="material-icons m-0">
                                                expand_more
                                            </i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            );
                        } else if (obj.tag == "a") {
                            return (
                                <div className="py-3" key={ind_obj}>
                                <div
                                    className="container"
                                >
                                    <input
                                        type="text"
                                        name={"tag" + (ind_obj + 1)}
                                        value={obj.tag}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                        }}
                                    />
                                    <input
                                        type="text"
                                        name={"link" + (ind_obj + 1)}
                                        placeholder="link"
                                        value={obj.link}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'link', ind_obj)
                                        }}
                                    />
                                    <input
                                        type="text"
                                        name={"teks" + (ind_obj + 1)}
                                        placeholder="teks"
                                        value={obj.teks}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'teks', ind_obj)
                                        }}
                                    />
                                    <input
                                        type="text"
                                        name={"style" + (ind_obj + 1)}
                                        placeholder="style"
                                        value={obj.style}
                                        onChange={(e) => {
                                            handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                        }}
                                    />
                                    <div className="d-flex gap-1">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                hapusIsi(ind_obj);
                                            }}
                                        >
                                            hapus
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                geserAtas(ind_obj);
                                            }}
                                        >
                                            <i className="material-icons m-0">
                                                expand_less
                                            </i>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                geserBawah(ind_obj);
                                            }}
                                        >
                                            <i className="material-icons m-0">
                                                expand_more
                                            </i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            );
                        } else if (obj.tag == "img") {
                            return (
                                <div className="py-3" key={ind_obj}>
                                    <div
                                        className="container"
                                    >
                                        <input
                                            type="text"
                                            name={"tag" + (ind_obj + 1)}
                                            value={obj.tag}
                                            onChange={(e) => {
                                                handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                            }}
                                        />
                                        <input
                                            type="file"
                                            name={"file" + (ind_obj + 1)}
                                        />
                                        <input
                                            type="text"
                                            name={"style" + (ind_obj + 1)}
                                            placeholder="style"
                                            value={obj.style}
                                            onChange={(e) => {
                                                handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                            }}
                                        />
                                        <div className="d-flex gap-1">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    hapusIsi(ind_obj);
                                                }}
                                            >
                                                hapus
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserAtas(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_less
                                                </i>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserBawah(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_more
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        } else if (obj.tag == "space") {
                            return (
                                <div className="py-3" key={ind_obj}>
                                    <div
                                        className="container"
                                    >
                                        <input
                                            type="text"
                                            name={"tag" + (ind_obj + 1)}
                                            value={obj.tag}
                                            onChange={(e) => {
                                                handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                            }}
                                        />
                                        <div className="d-flex gap-1">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    hapusIsi(ind_obj);
                                                }}
                                            >
                                                hapus
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserAtas(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_less
                                                </i>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserBawah(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_more
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        } else if (obj.tag == "div") {
                            return (
                                <div className="py-3" key={ind_obj}>
                                    <div className="container border">
                                        <div className="d-flex flex-column gap-2">
                                            {arr[ind_obj].children.map((obj_div, ind_obj_div)=>{
                                                if (
                                                    obj_div.tag == "p" ||
                                                    obj_div.tag == "h2" ||
                                                    obj_div.tag == "h4"
                                                ) {
                                                    return (
                                                    <div className="py-3" key={ind_obj}>
                                                        <div className="container">
                                                            <input
                                                                type="text"
                                                                name={"tag" + (ind_obj + 1)}
                                                                value={obj_div.tag}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                                                }}
                                                            />
                                                            <textarea
                                                                className="w-100 mt-1"
                                                                placeholder="teks"
                                                                name={"teks" + (ind_obj + 1)}
                                                                value={obj_div.teks}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'teks', ind_obj)
                                                                }}
                                                            ></textarea>
                                                            <input
                                                                type="text"
                                                                name={"style" + (ind_obj + 1)}
                                                                placeholder="style"
                                                                value={obj_div.style}
                                                                className="mb-1"
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                                                }}
                                                            />
                                                            <div className="d-flex gap-1">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        hapusIsi(ind_obj);
                                                                    }}
                                                                >
                                                                    hapus
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        geserAtas(ind_obj);
                                                                    }}
                                                                >
                                                                    <i className="material-icons m-0">
                                                                        expand_less
                                                                    </i>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        geserBawah(ind_obj);
                                                                    }}
                                                                >
                                                                    <i className="material-icons m-0">
                                                                        expand_more
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    );
                                                } else if (obj_div.tag == "a") {
                                                    return (
                                                        <div className="py-3" key={ind_obj}>
                                                        <div
                                                            className="container"
                                                        >
                                                            <input
                                                                type="text"
                                                                name={"tag" + (ind_obj + 1)}
                                                                value={obj_div.tag}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                                                }}
                                                            />
                                                            <input
                                                                type="text"
                                                                name={"link" + (ind_obj + 1)}
                                                                placeholder="link"
                                                                value={obj_div.link}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'link', ind_obj)
                                                                }}
                                                            />
                                                            <input
                                                                type="text"
                                                                name={"teks" + (ind_obj + 1)}
                                                                placeholder="teks"
                                                                value={obj_div.teks}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'teks', ind_obj)
                                                                }}
                                                            />
                                                            <input
                                                                type="text"
                                                                name={"style" + (ind_obj + 1)}
                                                                placeholder="style"
                                                                value={obj_div.style}
                                                                onChange={(e) => {
                                                                    handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                                                }}
                                                            />
                                                            <div className="d-flex gap-1">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        hapusIsi(ind_obj);
                                                                    }}
                                                                >
                                                                    hapus
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        geserAtas(ind_obj);
                                                                    }}
                                                                >
                                                                    <i className="material-icons m-0">
                                                                        expand_less
                                                                    </i>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        geserBawah(ind_obj);
                                                                    }}
                                                                >
                                                                    <i className="material-icons m-0">
                                                                        expand_more
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    );
                                                } else if (obj_div.tag == "img") {
                                                    return (
                                                        <div className="py-3" key={ind_obj}>
                                                            <div
                                                                className="container"
                                                            >
                                                                <input
                                                                    type="text"
                                                                    name={"tag" + (ind_obj + 1)}
                                                                    value={obj_div.tag}
                                                                    onChange={(e) => {
                                                                        handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                                                    }}
                                                                />
                                                                <input
                                                                    type="file"
                                                                    name={"file" + (ind_obj + 1)}
                                                                />
                                                                <input
                                                                    type="text"
                                                                    name={"style" + (ind_obj + 1)}
                                                                    placeholder="style"
                                                                    value={obj_div.style}
                                                                    onChange={(e) => {
                                                                        handleChangeInputIsi(e.target.value, 'style', ind_obj)
                                                                    }}
                                                                />
                                                                <div className="d-flex gap-1">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            hapusIsi(ind_obj);
                                                                        }}
                                                                    >
                                                                        hapus
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            geserAtas(ind_obj);
                                                                        }}
                                                                    >
                                                                        <i className="material-icons m-0">
                                                                            expand_less
                                                                        </i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            geserBawah(ind_obj);
                                                                        }}
                                                                    >
                                                                        <i className="material-icons m-0">
                                                                            expand_more
                                                                        </i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                } else if (obj_div.tag == "space") {
                                                    return (
                                                        <div className="py-3" key={ind_obj}>
                                                            <div
                                                                className="container"
                                                            >
                                                                <input
                                                                    type="text"
                                                                    name={"tag" + (ind_obj + 1)}
                                                                    value={obj_div.tag}
                                                                    onChange={(e) => {
                                                                        handleChangeInputIsi(e.target.value, 'tag', ind_obj)
                                                                    }}
                                                                />
                                                                <div className="d-flex gap-1">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            hapusIsi(ind_obj);
                                                                        }}
                                                                    >
                                                                        hapus
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            geserAtas(ind_obj);
                                                                        }}
                                                                    >
                                                                        <i className="material-icons m-0">
                                                                            expand_less
                                                                        </i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => {
                                                                            geserBawah(ind_obj);
                                                                        }}
                                                                    >
                                                                        <i className="material-icons m-0">
                                                                            expand_more
                                                                        </i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                }
                                            })}
                                        </div>
                                        <hr />
                                        <div className="d-flex gap-2 border-top py-3">
                                            <select id="select-tag" value={selectTag} onChange={(e)=>{setSelectTag(e.target.value)}}>
                                                <option value="">
                                                    -- pilih tag --
                                                </option>
                                                <option value="h2">h2</option>
                                                <option value="h4">h3</option>
                                                <option value="p">p</option>
                                                <option value="a">a</option>
                                                <option value="space">space</option>
                                            </select>
                                            <button type="button" onClick={()=>{tambahIsi(ind_obj)}}>
                                                Tambahkan
                                            </button>
                                        </div>
                                        <div className="d-flex gap-1">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    hapusIsi(ind_obj);
                                                }}
                                            >
                                                hapus
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserAtas(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_less
                                                </i>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    geserBawah(ind_obj);
                                                }}
                                            >
                                                <i className="material-icons m-0">
                                                    expand_more
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        }
                    })}
                </div>
                <hr />
                <div className="container">
                    <div className="d-flex gap-2 border-top py-3">
                        <select id="select-tag" value={selectTag} onChange={(e)=>{setSelectTag(e.target.value)}}>
                            <option value="">
                                -- pilih tag --
                            </option>
                            <option value="h2">h2</option>
                            <option value="h4">h3</option>
                            <option value="p">p</option>
                            {/* <option value="img">img</option> */}
                            <option value="a">a</option>
                            <option value="space">space</option>
                            <option value="div">div</option>
                        </select>
                        <button type="button" onClick={tambahIsi}>
                            Tambahkan
                        </button>
                    </div>
                    <div className="d-flex justify-content-center mt-3">
                        <button className="btn btn-primary1" type="submit">
                            Simpan
                        </button>
                    </div>
                    <input
                        type="text"
                        className="d-none"
                        name="arrCounter"
                        value={arrCounterElm}
                        onChange={(e) => {
                            console.log(e.target.value);
                        }}
                    />
                </div>

                

                </>
            );
        };

        ReactDOM.render(<App />, document.getElementById("container-react"));
    </script>
<?php } ?>
<?= $this->endSection(); ?>