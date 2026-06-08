import { read, utils, write } from 'xlsx-js-style'

const excelTransformer = {
  mimeTypeXlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  mimeTypeXls: 'application/vnd.ms-excel',
  getSupportedMimeTypes: function () {
    return [this.mimeTypeXlsx, this.mimeTypeXls]
  },
  exportToXlsx: function (content, header, worksheetName) {
    const workbook = utils.book_new()
    const worksheet = utils.aoa_to_sheet([header, ...content])

    header.forEach((_, index) => {
      const cellAddress = utils.encode_cell({
        r: 0,
        c: index
      })
      if (worksheet[cellAddress]) {
        worksheet[cellAddress].s = {
          font: { bold: true }
        }
      }
    })

    worksheet['!cols'] = header.map((_, index) => {
      const maxLength = Math.max(
        String(header[index] ?? '').length,
        ...content.map(row => String(row[index] ?? '').length)
      )

      return { wch: Math.min(Math.max(maxLength + 2, 12), 40) }
    })

    utils.book_append_sheet(workbook, worksheet, worksheetName)

    const blobPart = write(workbook, {
      bookType: 'xlsx',
      bookSST: false,
      type: 'array'
    })

    return new Blob([blobPart], { type: this.mimeTypeXlsx })
  },
  import: function (arrayBuffer) {
    const data = new Uint8Array(arrayBuffer)
    const workbook = read(data, { type: 'array' })
    const worksheet = workbook.Sheets[workbook.SheetNames[0]]

    return utils.sheet_to_json(worksheet, { header: 1 })
  }
}

export { excelTransformer }
